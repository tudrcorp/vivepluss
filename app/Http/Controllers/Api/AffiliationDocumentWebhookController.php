<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAffiliationDocumentWebhookRequest;
use App\Jobs\SendAffiliationDocumentWhatsApp;
use App\Mail\AffiliationDocumentAvailableMail;
use App\Models\Affiliate;
use App\Models\AffiliateCorporate;
use App\Models\Affiliation;
use App\Models\AffiliationCorporate;
use App\Models\AffiliationDocument;
use App\Models\Configuration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Recibe el certificado de afiliación y el carnet del afiliado que
 * Integracorp genera al ejecutar "Regenerar documentos" y los deja
 * disponibles para descarga de los analistas de ViVEplus. Único punto de
 * entrada de esos documentos: ViVEplus ya no los genera por su cuenta.
 */
class AffiliationDocumentWebhookController extends Controller
{
    private const DISK = 'public';

    public function store(StoreAffiliationDocumentWebhookRequest $request)
    {
        $affiliationType = $request->string('affiliation_type')->toString();
        $affiliationCode = $request->string('affiliation_code')->toString();
        $documentType = $request->string('document_type')->toString();
        // El certificado es único por afiliación (identificación vacía); el
        // carnet es por persona, así que aquí sí viaja el nro_identificacion.
        $affiliateIdentification = $documentType === AffiliationDocument::TYPE_CARNET
            ? $request->string('affiliate_identification')->toString()
            : '';
        $generatedAt = $request->date('generated_at');
        $idempotencyKey = $request->string('idempotency_key')->toString();

        $existing = AffiliationDocument::latestFor($affiliationCode, $documentType, $affiliateIdentification);

        if ($existing && $existing->idempotency_key === $idempotencyKey) {
            Log::info('Webhook de documentos de Integracorp: entrega duplicada, ignorada.', [
                'affiliation_code' => $affiliationCode,
                'document_type' => $documentType,
                'affiliate_identification' => $affiliateIdentification,
                'idempotency_key' => $idempotencyKey,
            ]);

            return response()->json(['message' => 'Ya procesado previamente.'], 409);
        }

        if ($existing && $generatedAt->lessThanOrEqualTo($existing->generated_at)) {
            Log::info('Webhook de documentos de Integracorp: versión desactualizada, ignorada.', [
                'affiliation_code' => $affiliationCode,
                'document_type' => $documentType,
                'affiliate_identification' => $affiliateIdentification,
                'generated_at' => $generatedAt->toIso8601String(),
                'stored_generated_at' => $existing->generated_at->toIso8601String(),
            ]);

            return response()->json(['message' => 'Ya existe una versión más reciente almacenada.'], 409);
        }

        [$affiliationId, $affiliationCorporateId] = $this->resolveAffiliationIds($affiliationType, $affiliationCode);
        [$affiliateId, $affiliateCorporateId] = $this->resolveAffiliateIds($affiliationType, $affiliationCode, $affiliateIdentification);

        $diskPath = $this->storeFileAtomically($request, $affiliationCode, $documentType, $affiliateIdentification);

        $document = DB::transaction(function () use (
            $affiliationType,
            $affiliationId,
            $affiliationCorporateId,
            $affiliateId,
            $affiliateCorporateId,
            $affiliateIdentification,
            $affiliationCode,
            $documentType,
            $diskPath,
            $request,
            $generatedAt,
            $idempotencyKey,
        ) {
            return AffiliationDocument::updateOrCreate(
                [
                    'affiliation_code' => $affiliationCode,
                    'document_type' => $documentType,
                    'affiliate_identification' => $affiliateIdentification,
                ],
                [
                    'affiliation_kind' => $affiliationType,
                    'affiliation_id' => $affiliationId,
                    'affiliation_corporate_id' => $affiliationCorporateId,
                    'affiliate_id' => $affiliateId,
                    'affiliate_corporate_id' => $affiliateCorporateId,
                    'disk' => self::DISK,
                    'disk_path' => $diskPath,
                    'checksum_sha256' => strtolower($request->input('checksum_sha256')),
                    'generated_at' => $generatedAt,
                    'received_at' => now(),
                    'idempotency_key' => $idempotencyKey,
                ]
            );
        });

        Log::info('Webhook de documentos de Integracorp: documento almacenado.', [
            'affiliation_code' => $affiliationCode,
            'document_type' => $documentType,
            'affiliate_identification' => $affiliateIdentification,
            'idempotency_key' => $idempotencyKey,
        ]);

        $this->notifyAnalysts($document);

        return response()->json(['message' => 'Documento almacenado.'], 201);
    }

    /**
     * Avisa a los analistas de ViVEplus (WhatsApp + email, configurados por
     * marca blanca en Configuration) de que un documento de Integracorp
     * quedó disponible para descarga. Una notificación por documento
     * recibido: no se espera a que estén el certificado y todos los
     * carnets de la afiliación. Un fallo aquí no debe afectar la respuesta
     * 201 ya construida para Integracorp, así que corre en su propio
     * try/catch.
     */
    private function notifyAnalysts(AffiliationDocument $document): void
    {
        try {
            $whiteCompanyId = $document->affiliation_kind === AffiliationDocument::KIND_INDIVIDUAL
                ? $document->affiliation?->white_company_id
                : $document->affiliationCorporate?->white_company_id;

            $configuration = Configuration::where('white_company_id', $whiteCompanyId)->first();

            if (! $configuration || ! $configuration->document_notifications_enabled) {
                return;
            }

            $emails = $configuration->document_notification_emails ?? [];
            $phones = $configuration->document_notification_phones ?? [];

            if (filled($emails)) {
                Mail::to($emails)->queue(new AffiliationDocumentAvailableMail(
                    $document,
                    $configuration->white_company_name ?? 'N/A',
                ));
            }

            if (filled($phones)) {
                $label = $document->document_type === AffiliationDocument::TYPE_CARNET ? 'Carnet' : 'Certificado';
                $body = "📄 {$label} disponible en ViVEplus\n\nAfiliación: {$document->affiliation_code}\nYa puedes descargarlo desde el panel de ViVEplus.";

                foreach ($phones as $phone) {
                    SendAffiliationDocumentWhatsApp::dispatch($phone, $body);
                }
            }
        } catch (\Throwable $th) {
            Log::error('No se pudo notificar la disponibilidad del documento de Integracorp: '.$th->getMessage(), [
                'affiliation_code' => $document->affiliation_code,
                'document_type' => $document->document_type,
            ]);
        }
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    private function resolveAffiliationIds(string $affiliationType, string $affiliationCode): array
    {
        if ($affiliationType === AffiliationDocument::KIND_INDIVIDUAL) {
            $id = Affiliation::where('code', $affiliationCode)->value('id');

            return [$id, null];
        }

        $id = AffiliationCorporate::where('code', $affiliationCode)->value('id');

        return [null, $id];
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    private function resolveAffiliateIds(string $affiliationType, string $affiliationCode, string $affiliateIdentification): array
    {
        if (blank($affiliateIdentification)) {
            return [null, null];
        }

        if ($affiliationType === AffiliationDocument::KIND_INDIVIDUAL) {
            $id = Affiliate::where('nro_identificacion', $affiliateIdentification)
                ->whereHas('affiliation', fn ($query) => $query->where('code', $affiliationCode))
                ->value('id');

            return [$id, null];
        }

        $id = AffiliateCorporate::where('nro_identificacion', $affiliateIdentification)
            ->whereHas('affiliationCorporate', fn ($query) => $query->where('code', $affiliationCode))
            ->value('id');

        return [null, $id];
    }

    /**
     * Escribe el PDF recibido en el disco con un reemplazo atómico
     * (temporal + rename) para que un analista nunca descargue un archivo
     * a medio escribir si dos entregas llegan cerca en el tiempo. El nombre
     * incluye la identificación del afiliado cuando aplica (carnet), para
     * que el carnet de un miembro de la familia no pise el de otro.
     */
    private function storeFileAtomically(StoreAffiliationDocumentWebhookRequest $request, string $affiliationCode, string $documentType, string $affiliateIdentification): string
    {
        $directory = "documentos-integracorp/{$documentType}s";
        $baseName = filled($affiliateIdentification) ? "{$affiliationCode}-{$affiliateIdentification}" : $affiliationCode;
        $finalPath = "{$directory}/{$baseName}.pdf";
        $tempPath = "{$directory}/.tmp-{$baseName}-".Str::uuid()->toString().'.pdf';

        Storage::disk(self::DISK)->makeDirectory($directory);

        $request->file('file')->storeAs($directory, basename($tempPath), self::DISK);

        Storage::disk(self::DISK)->move($tempPath, $finalPath);

        return $finalPath;
    }
}
