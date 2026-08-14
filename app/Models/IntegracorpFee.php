<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catálogo propio de tarifas de Integracorp (tabla `fees` en la BD compartida
 * `operaciones`), distinto del catálogo `App\Models\Fee` de ViVEplus (conexión
 * `mysql_vivepluss`). `WhiteCompanyFee::fee_id` referencia este catálogo, no el
 * propio de ViVEplus, aunque los IDs de plan/cobertura coincidan entre ambos.
 */
class IntegracorpFee extends Model
{
    protected $table = 'fees';

    public function ageRange(): BelongsTo
    {
        return $this->belongsTo(IntegracorpAgeRange::class, 'age_range_id');
    }

    public function whiteCompanyFees(): HasMany
    {
        return $this->hasMany(WhiteCompanyFee::class, 'fee_id');
    }
}
