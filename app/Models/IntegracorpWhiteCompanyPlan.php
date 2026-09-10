<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Planes que Integracorp habilitó para una empresa aliada (tabla `white_company_plans` en la BD compartida `operaciones`).
 * Solo lectura: lo consume la sincronización de planes asignados.
 */
class IntegracorpWhiteCompanyPlan extends Model
{
    protected $table = 'white_company_plans';
}
