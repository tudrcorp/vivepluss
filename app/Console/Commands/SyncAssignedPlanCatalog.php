<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Catalog\AssignedPlanCatalogSync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Trae al catálogo de ViVEplus los planes que Integracorp le asignó en la matriz
 * de negociación, para que queden cotizables sin cargarlos a mano.
 */
class SyncAssignedPlanCatalog extends Command
{
    protected $signature = 'catalog:sync-assigned-plans {--white-company= : Id de la empresa aliada (por defecto, la del panel)}';

    protected $description = 'Sincroniza desde Integracorp los planes y tarifas asignados a la empresa aliada';

    public function handle(): int
    {
        $whiteCompanyId = $this->option('white-company');

        $resumen = AssignedPlanCatalogSync::run(
            $whiteCompanyId !== null ? (int) $whiteCompanyId : null,
        );

        if ($resumen['white_company_id'] === null) {
            $this->error('No se pudo resolver la empresa aliada. Configure el white_company_id o pase --white-company.');

            return self::FAILURE;
        }

        if ($resumen['planes_asignados'] === 0) {
            $this->warn(sprintf(
                'La empresa aliada %d no tiene planes asignados en Integracorp: no hay nada que sincronizar.',
                $resumen['white_company_id'],
            ));

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Empresa aliada %d: %d planes asignados (%d creados, %d ya existentes), %d beneficios, %d coberturas, %d rangos.',
            $resumen['white_company_id'],
            $resumen['planes_asignados'],
            $resumen['planes_creados'],
            $resumen['planes_existentes'],
            $resumen['beneficios'],
            $resumen['coberturas'],
            $resumen['rangos'],
        ));

        $this->info(sprintf(
            'Tarifas con neta pactada: %d creadas, %d actualizadas, %d sin cambio.',
            $resumen['tarifas_creadas'],
            $resumen['tarifas_actualizadas'],
            $resumen['tarifas_sin_cambio'],
        ));

        foreach ($resumen['omitidas'] as $omitida) {
            $this->warn('Omitida '.$omitida);
        }

        Log::info('catalog:sync-assigned-plans', $resumen);

        return self::SUCCESS;
    }
}
