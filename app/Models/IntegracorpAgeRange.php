<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Rangos de edad del catálogo propio de Integracorp (tabla `age_ranges` en la
 * BD compartida `operaciones`), usados para resolver `IntegracorpFee`. Distinto
 * del `App\Models\AgeRange` de ViVEplus (conexión `mysql_vivepluss`).
 */
class IntegracorpAgeRange extends Model
{
    protected $table = 'age_ranges';
}
