<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Planes del catálogo propio de Integracorp (tabla `plans` en la BD compartida
 * `operaciones`). Distinto del `App\Models\Plan` de ViVEplus (conexión
 * `mysql_vivepluss`), que es el que se cotiza. Solo lectura: lo consume la
 * sincronización de planes asignados.
 */
class IntegracorpPlan extends Model
{
    protected $table = 'plans';
}
