<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Detalle (coberturas/tarifas por rango de edad) de las cotizaciones
 * individuales legacy de Integracorp (tabla `detail_individual_quotes` en la
 * BD compartida `operaciones`). Ver App\Models\IntegracorpIndividualQuote.
 */
class IntegracorpDetailIndividualQuote extends Model
{
    protected $table = 'detail_individual_quotes';

    /**
     * Ver App\Models\IntegracorpIndividualQuote: el guard solo actúa contra
     * la conexión real `mysql` (operaciones), no contra la tabla sqlite
     * efímera que usan los tests.
     */
    protected static function booted(): void
    {
        $guard = function (self $model) {
            if ($model->getConnection()->getName() === 'mysql') {
                throw new \RuntimeException('detail_individual_quotes de Integracorp es de solo lectura desde ViVEplus.');
            }
        };

        static::saving($guard);
        static::deleting($guard);
    }
}
