<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        return static::where('affiliation_code', $affiliationCode)
            ->where('document_type', $documentType)
            ->where('affiliate_identification', $affiliateIdentification)
            ->first();
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
