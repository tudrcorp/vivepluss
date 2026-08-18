<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Originalmente iba a crear `corporate_quote_request_observations` en mysql
 * (schema compartido de Integracorp). Nunca llegó a ejecutarse: la tabla no
 * existe en producción (`integracorp_produccion`) y las migraciones de esa
 * conexión no se aplican ahí. La tabla vive ahora en mysql_vivepluss, ver
 * 2026_08_18_150000_create_vivepluss_corporate_quote_request_observations_table.
 */
return new class extends Migration
{
    public function up(): void
    {
        // no-op: no crear tablas nuevas en el schema compartido de Integracorp
    }

    public function down(): void
    {
        //
    }
};
