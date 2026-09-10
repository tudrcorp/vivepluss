<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Beneficios por plan del catálogo de Integracorp (tabla `benefit_plans` en la BD compartida `operaciones`).
 * Solo lectura: lo consume la sincronización de planes asignados.
 */
class IntegracorpBenefitPlan extends Model
{
    protected $table = 'benefit_plans';
}
