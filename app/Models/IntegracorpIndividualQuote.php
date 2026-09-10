<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Cotizaciones individuales legacy de Integracorp (tabla `individual_quotes`
 * en la BD compartida `operaciones`, conexión `mysql`). Distinta de
 * `App\Models\IndividualQuote`, que desde la migración
 * 2026_08_17_180000_create_vivepluss_individual_quotes_tables vive en la
 * conexión propia `mysql_vivepluss` -esta tabla legacy sigue existiendo tal
 * cual, sin recibir escrituras nuevas de ViVEplus nunca más (ver guardas
 * abajo). Se usa exclusivamente para leer cotizaciones creadas antes del
 * corte, vía App\Support\IndividualQuoteResolver.
 */
class IntegracorpIndividualQuote extends Model
{
    protected $table = 'individual_quotes';

    /**
     * El guard solo actúa contra la conexión real `mysql` (operaciones):
     * en tests, esta misma clase se usa contra una tabla sqlite efímera
     * (conexión por defecto en testing, ver phpunit.xml) que no es la
     * tabla real de Integracorp, y ahí sí debe poder escribirse.
     */
    protected static function booted(): void
    {
        $guard = function (self $model) {
            if ($model->getConnection()->getName() === 'mysql') {
                throw new \RuntimeException('individual_quotes de Integracorp es de solo lectura desde ViVEplus.');
            }
        };

        static::saving($guard);
        static::deleting($guard);
    }
}
