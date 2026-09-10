<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Coberturas del catálogo propio de Integracorp (tabla `coverages` en la BD
 * compartida `operaciones`). Distinto del `App\Models\Coverage` de ViVEplus.
 * Solo lectura: lo consume la sincronización de planes asignados.
 */
class IntegracorpCoverage extends Model
{
    protected $table = 'coverages';
}
