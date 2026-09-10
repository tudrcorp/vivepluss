<?php

namespace App\Models\Concerns;

/**
 * Fija la conexión por defecto en vez de dejarla en null.
 *
 * Eloquent, en newRelatedInstance(), hereda la conexión del modelo padre
 * cuando getConnectionName() es null. Tras el corte de cotizaciones a
 * mysql_vivepluss, relaciones hacia tablas de Integracorp (agents, users,
 * agencies, states, …) acababan consultando viveplus_db, donde esas
 * tablas no existen.
 *
 * Se usa config('database.default') y no el nombre 'mysql' para que en
 * tests (sqlite en memoria) el modelo siga apuntando a la conexión de
 * phpunit.xml.
 */
trait UsesDefaultConnection
{
    public function getConnectionName()
    {
        return $this->connection ?? config('database.default');
    }
}
