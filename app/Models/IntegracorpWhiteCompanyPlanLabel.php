<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Nombre comercial que Integracorp le da a un plan para una empresa aliada
 * (tabla `white_company_plan_labels` en `operaciones`). Se usa como nombre
 * inicial de los planes que la sincronización crea en ViVEplus.
 */
class IntegracorpWhiteCompanyPlanLabel extends Model
{
    protected $table = 'white_company_plan_labels';
}
