<?php

namespace App\Console\Commands;

use App\Models\Affiliate;
use App\Models\AffiliateCorporate;
use App\Models\Affiliation;
use App\Models\AffiliationCorporate;
use App\Models\AffiliationDocument;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Certificado y carnet ahora dependen 100% de que Integracorp los entregue
 * vía webhook al ejecutar "Regenerar documentos" — ViVEplus ya no tiene un
 * generador propio de respaldo. El certificado es uno solo por afiliación;
 * el carnet es uno por afiliado (titular y cada dependiente), así que hay
 * que revisar cada persona por separado, no solo la afiliación como bloque.
 * Este comando detecta lo que lleva más tiempo del tolerado sin llegar, para
 * poder actuar antes de que un analista lo note al intentar descargar.
 */
class CheckMissingAffiliationDocuments extends Command
{
    protected $signature = 'documents:check-missing';

    protected $description = 'Alerta sobre certificados/carnets sin recibir de Integracorp más allá del tiempo de tolerancia configurado';

    public function handle(): int
    {
        if (! Schema::hasTable((new AffiliationDocument)->getTable())) {
            $this->warn('La tabla affiliation_integracorp_documents no existe. Ejecutar php artisan migrate.');

            return self::SUCCESS;
        }

        $hours = (int) config('parametros.DOCUMENT_SYNC_ALERT_HOURS', 48);
        $cutoff = now()->subHours($hours);

        $missing = $this->missingFor(
            Affiliation::query()->where('created_at', '<=', $cutoff)->where('status', '!=', 'EXCLUIDO'),
            AffiliationDocument::KIND_INDIVIDUAL,
            Affiliate::class,
            'affiliation_id'
        )->merge($this->missingFor(
            AffiliationCorporate::query()->where('created_at', '<=', $cutoff)->where('status', '!=', 'EXCLUIDO'),
            AffiliationDocument::KIND_CORPORATE,
            AffiliateCorporate::class,
            'affiliation_corporate_id'
        ));

        if ($missing->isEmpty()) {
            $this->info('Sin certificados/carnets pendientes de Integracorp.');

            return self::SUCCESS;
        }

        Log::warning('Certificados/carnets de Integracorp pendientes más allá del tiempo de tolerancia.', [
            'threshold_hours' => $hours,
            'affiliations' => $missing->all(),
        ]);

        $this->warn(sprintf('%d afiliación(es) con documentos pendientes de Integracorp (detalle en el log).', $missing->count()));

        return self::SUCCESS;
    }

    /**
     * @param  class-string  $affiliateModel
     * @return Collection<int, array{affiliation_kind: string, code: string, missing_certificado: bool, missing_carnet_for: array<int, string>}>
     */
    private function missingFor(Builder $query, string $kind, string $affiliateModel, string $affiliationForeignKey): Collection
    {
        $records = $query->get(['id', 'code']);

        if ($records->isEmpty()) {
            return collect();
        }

        $codes = $records->pluck('code');
        $idsByCode = $records->pluck('id', 'code');

        $certificadosByCode = AffiliationDocument::whereIn('affiliation_code', $codes)
            ->where('document_type', AffiliationDocument::TYPE_CERTIFICADO)
            ->pluck('affiliation_code')
            ->flip();

        $carnetsByCode = AffiliationDocument::whereIn('affiliation_code', $codes)
            ->where('document_type', AffiliationDocument::TYPE_CARNET)
            ->get(['affiliation_code', 'affiliate_identification'])
            ->groupBy('affiliation_code')
            ->map(fn (Collection $rows) => $rows->pluck('affiliate_identification')->all());

        $affiliatesByAffiliationId = $affiliateModel::whereIn($affiliationForeignKey, $idsByCode->values())
            ->get([$affiliationForeignKey, 'nro_identificacion'])
            ->groupBy($affiliationForeignKey);

        return $records
            ->map(function ($record) use ($certificadosByCode, $carnetsByCode, $affiliatesByAffiliationId, $kind) {
                $missingCertificado = ! $certificadosByCode->has($record->code);

                $affiliateIdentifications = ($affiliatesByAffiliationId->get($record->id) ?? collect())
                    ->pluck('nro_identificacion')
                    ->filter()
                    ->values();

                $carnetsReceived = $carnetsByCode->get($record->code, []);
                $missingCarnetFor = $affiliateIdentifications->diff($carnetsReceived)->values()->all();

                if (! $missingCertificado && $missingCarnetFor === []) {
                    return null;
                }

                return [
                    'affiliation_kind' => $kind,
                    'code' => $record->code,
                    'missing_certificado' => $missingCertificado,
                    'missing_carnet_for' => $missingCarnetFor,
                ];
            })
            ->filter()
            ->values();
    }
}
