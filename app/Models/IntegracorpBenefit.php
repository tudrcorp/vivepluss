<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Beneficios del catálogo propio de Integracorp (tabla `benefits` en la BD compartida `operaciones`).
 * Solo lectura: lo consume la sincronización de planes asignados.
 */
class IntegracorpBenefit extends Model
{
    protected $table = 'benefits';
}
