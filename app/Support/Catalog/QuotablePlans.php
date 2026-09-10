<?php

declare(strict_types=1);

namespace App\Support\Catalog;

use App\Models\AgeRange;
use App\Models\Fee;
use App\Models\Plan;

/**
 * Planes que se ofrecen en los formularios de cotización.
 *
 * Los tres planes históricos (Inicial, Ideal, Especial) tienen cada uno su
 * propio repeater en el paso de rangos de edad, escrito a mano. Cualquier otro
 * plan —los que Integracorp asigna a la aliada y llegan por
 * `AssignedPlanCatalogSync`— usa el repeater genérico, que se arma con los
 * rangos del plan elegido. Sin eso, elegir un plan asignado dejaba el paso de
 * edades vacío y la cotización no se podía completar.
 */
final class QuotablePlans
{
    /** Planes con repeater propio en el formulario. */
    public const LEGACY_PLAN_IDS = [1, 2, 3];

    /** Opción de cotización múltiple. */
    public const MULTIPLE = 'CM';

    /**
     * Opciones del selector de planes: los cotizables más la cotización múltiple.
     *
     * @return array<int|string, string>
     */
    public static function options(): array
    {
        $planes = Plan::cotizable()->get()->pluck('description', 'id')->all();
        $planes[self::MULTIPLE] = 'COTIZACIÓN MULTIPLE';

        return $planes;
    }

    /**
     * Descripciones bajo cada opción, con el rango de edades real del plan en
     * lugar de un texto fijo por plan.
     *
     * @return array<int|string, string>
     */
    public static function descriptions(): array
    {
        $descripciones = [];

        foreach (array_keys(Plan::cotizable()->get()->pluck('description', 'id')->all()) as $planId) {
            $descripciones[$planId] = self::ageDescription((int) $planId);
        }

        $descripciones[self::MULTIPLE] = 'Seleccione más de dos (2) planes.';

        return $descripciones;
    }

    /**
     * Rangos de edad ofrecibles de un plan: solo los que tienen tarifa.
     *
     * El catálogo local arrastra rangos viejos de planes que no existían (por
     * ejemplo el plan 16 tenía un "90 A 100" sin ninguna tarifa). Ofrecerlos
     * dejaba elegir un rango que después no resolvía precio y la cotización
     * salía vacía, sin error visible.
     *
     * @return array<int, string>
     */
    public static function ageRangeOptions(mixed $planId): array
    {
        if (blank($planId)) {
            return [];
        }

        return AgeRange::query()
            ->where('plan_id', $planId)
            ->whereIn('id', Fee::query()->where('plan_id', $planId)->select('age_range_id'))
            ->pluck('range', 'id')
            ->all();
    }

    public static function ageRangeCount(mixed $planId): int
    {
        return count(self::ageRangeOptions($planId));
    }

    /**
     * Nombre del plan de una cotización, para mostrarlo en las tablas del panel.
     *
     * Sale del catálogo en vez de una lista escrita a mano: los planes que
     * Integracorp asigna a la aliada no están en ninguna lista fija, y antes
     * caían en un `null` que dejaba la columna vacía.
     */
    public static function quoteLabel(mixed $plan): string
    {
        if (blank($plan)) {
            return '-----';
        }

        if ($plan === self::MULTIPLE) {
            return 'MultiPlan';
        }

        $descripcion = Plan::query()->whereKey($plan)->value('description');

        return filled($descripcion) ? (string) $descripcion : 'Plan '.$plan;
    }

    /**
     * Etiqueta corta de una cobertura para la cabecera del PDF: 1000 → "1K",
     * 20000 → "20K". Es como están escritas a mano en las propuestas de los
     * planes históricos, así que la propuesta de un plan asignado se lee igual.
     */
    public static function coverageLabel(mixed $price): string
    {
        $monto = (float) $price;

        if ($monto >= 1000 && fmod($monto, 1000.0) === 0.0) {
            return (int) ($monto / 1000).'K';
        }

        return rtrim(rtrim(number_format($monto, 2, ',', '.'), '0'), ',');
    }

    public static function ageDescription(int $planId): string
    {
        $rangos = AgeRange::query()
            ->where('plan_id', $planId)
            ->whereIn('id', Fee::query()->where('plan_id', $planId)->select('age_range_id'))
            ->get();

        $min = $rangos->min('age_init');
        $max = $rangos->max('age_end');

        if ($min === null || $max === null) {
            return 'Consulte los rangos de edad disponibles.';
        }

        return sprintf('Edad: %d a %d años.', (int) $min, (int) $max);
    }

    /**
     * ¿El plan elegido usa el repeater genérico? Es decir, no es uno de los tres
     * históricos ni la cotización múltiple.
     */
    public static function usesGenericRepeater(mixed $plan): bool
    {
        if (blank($plan) || $plan === self::MULTIPLE) {
            return false;
        }

        return ! in_array((int) $plan, self::LEGACY_PLAN_IDS, true);
    }

    /**
     * Filas del formulario para el plan elegido, con su `plan_id` puesto. El
     * repeater genérico no lo lleva como campo porque el plan se elige en el
     * paso anterior.
     *
     * @param  array<int, array<string, mixed>>|null  $items
     * @return array<int, array<string, mixed>>
     */
    public static function withPlanId(?array $items, mixed $plan): array
    {
        return collect($items ?? [])
            ->map(function (mixed $item) use ($plan): array {
                $fila = is_array($item) ? $item : [];
                $fila['plan_id'] = (int) $plan;

                return $fila;
            })
            ->values()
            ->all();
    }
}
