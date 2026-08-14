<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Precio de venta / neta pactados entre Integracorp y una empresa aliada
 * (`WhiteCompany`) para una tarifa de su catálogo (`IntegracorpFee`).
 */
class WhiteCompanyFee extends Model
{
    protected $table = 'white_company_fees';

    protected $fillable = [
        'white_company_id',
        'fee_id',
        'sale_price',
        'neta',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sale_price' => 'decimal:2',
            'neta' => 'decimal:2',
        ];
    }

    public function whiteCompany(): BelongsTo
    {
        return $this->belongsTo(WhiteCompany::class);
    }

    public function fee(): BelongsTo
    {
        return $this->belongsTo(IntegracorpFee::class, 'fee_id');
    }
}
