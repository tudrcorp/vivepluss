<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AffiliationDocument extends Model
{
    protected $table = 'affiliation_integracorp_documents';

    protected $fillable = [
        'affiliation_kind',
        'affiliation_id',
        'affiliation_corporate_id',
        'affiliate_id',
        'affiliate_corporate_id',
        'affiliate_identification',
        'affiliation_code',
        'document_type',
        'disk',
        'disk_path',
        'checksum_sha256',
        'generated_at',
        'received_at',
        'idempotency_key',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    const KIND_INDIVIDUAL = 'individual';

    const KIND_CORPORATE = 'corporate';

    const TYPE_CERTIFICADO = 'certificado';

    const TYPE_CARNET = 'carnet';

    public function affiliation(): BelongsTo
    {
        return $this->belongsTo(Affiliation::class);
    }

    public function affiliationCorporate(): BelongsTo
    {
        return $this->belongsTo(AffiliationCorporate::class);
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function affiliateCorporate(): BelongsTo
    {
        return $this->belongsTo(AffiliateCorporate::class);
    }

    /**
     * Última versión disponible de un documento para una afiliación, la que
     * Integracorp haya entregado más recientemente vía el webhook de
     * regeneración de documentos. El certificado es uno solo por afiliación
     * ($affiliateIdentification se deja vacío); el carnet es por persona,
     * así que hay que indicar el nro_identificacion del afiliado puntual.
     */
    public static function latestFor(string $affiliationCode, string $documentType, string $affiliateIdentification = ''): ?self
    {
        try {
            return static::where('affiliation_code', $affiliationCode)
                ->where('document_type', $documentType)
                ->where('affiliate_identification', $affiliateIdentification)
                ->first();
        } catch (QueryException $e) {
            // Si la migración aún no corrió en este entorno, no tumbar el
            // listado de afiliaciones (el hidden() de Filament consulta esto
            // por cada fila). El webhook y el alta de documentos siguen
            // fallando más abajo al escribir, que es lo correcto.
            if (! static::isMissingDocumentsTable($e)) {
                throw $e;
            }

            static::warnMissingDocumentsTableOnce();

            return null;
        }
    }

    private static bool $missingTableWarned = false;

    private static function isMissingDocumentsTable(QueryException $e): bool
    {
        return $e->getCode() === '42S02'
            || str_contains($e->getMessage(), 'affiliation_integracorp_documents');
    }

    private static function warnMissingDocumentsTableOnce(): void
    {
        if (self::$missingTableWarned) {
            return;
        }

        self::$missingTableWarned = true;

        Log::warning('La tabla affiliation_integracorp_documents no existe. Ejecutar php artisan migrate en este entorno.');
    }

    public function existsOnDisk(): bool
    {
        return Storage::disk($this->disk)->exists($this->disk_path);
    }

    public function absolutePath(): string
    {
        return Storage::disk($this->disk)->path($this->disk_path);
    }
}
