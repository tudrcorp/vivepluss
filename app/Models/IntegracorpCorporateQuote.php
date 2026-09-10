<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Cotizaciones corporativas legacy de Integracorp (tabla `corporate_quotes`
 * en la BD compartida `operaciones`, conexión `mysql`). Distinta de
 * `App\Models\CorporateQuote`, que desde la migración
 * 2026_08_18_120000_create_vivepluss_corporate_quotes_tables vive en la
 * conexión propia `mysql_vivepluss` -esta tabla legacy sigue existiendo tal
 * cual, sin recibir escrituras nuevas de ViVEplus nunca más (ver guardas
 * abajo). Se usa exclusivamente para leer cotizaciones creadas antes del
 * corte, vía App\Support\CorporateQuoteResolver.
 */
class IntegracorpCorporateQuote extends Model
{
    protected $table = 'corporate_quotes';

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
                throw new \RuntimeException('corporate_quotes de Integracorp es de solo lectura desde ViVEplus.');
            }
        };

        static::saving($guard);
        static::deleting($guard);
    }
}
