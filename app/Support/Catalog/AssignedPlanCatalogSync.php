<?php

declare(strict_types=1);

namespace App\Support\Catalog;

use App\Models\AgeRange;
use App\Models\Benefit;
use App\Models\Configuration;
use App\Models\Coverage;
use App\Models\Fee;
use App\Models\IntegracorpAgeRange;
use App\Models\IntegracorpBenefit;
use App\Models\IntegracorpBenefitPlan;
use App\Models\IntegracorpCoverage;
use App\Models\IntegracorpFee;
use App\Models\IntegracorpPlan;
use App\Models\IntegracorpWhiteCompanyPlan;
use App\Models\IntegracorpWhiteCompanyPlanLabel;
use App\Models\Plan;
use App\Models\WhiteCompanyFee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Trae al catálogo propio de ViVEplus (`mysql_vivepluss`) los planes que el
 * analista de Integracorp le habilitó, para poder cotizarlos sin cargarlos a
 * mano.
 *
 * El circuito del lado de Integracorp tiene dos pasos y acá se respetan igual:
 *
 * 1. **Planes asignados** (`white_company_plans`) — qué planes puede cotizar la
 *    aliada. De ahí sale el plan con sus beneficios.
 * 2. **Matriz de negociación** (`white_company_fees`) — venta y neta por tarifa.
 *    De ahí sale el precio, y solo se traen las tarifas que tengan neta pactada.
 *
 * Por eso un plan asignado sin netas llega al catálogo (con sus beneficios) pero
 * **no se ofrece para cotizar**: sin precio de venta no hay nada que cotizar.
 * `Plan::scopeCotizable()` es el que aplica esa regla.
 *
 * **El precio que se sincroniza es `white_company_fees.sale_price`**, no el
 * precio de lista de Integracorp: lo que ViVEplus le cobra al cliente es la
 * venta pactada. La neta se guarda solo como referencia — las comisiones se
 * siguen resolviendo contra el catálogo de Integracorp en
 * `WhiteCompanyNegotiatedRateResolver`.
 *
 * Qué NO toca:
 * - El nombre de un plan que ya existe en ViVEplus. Los planes actuales tienen
 *   nombre comercial propio (ESENCIAL / BIENESTAR / PREMIUM) y no se pisan; solo
 *   se nombra a los que crea.
 * - Los beneficios ya cargados: se agregan los que falten, nunca se borran.
 * - Los condicionados PDF, que son configuración de ViVEplus.
 */
final class AssignedPlanCatalogSync
{
    /**
     * @return array{
     *     white_company_id: int|null,
     *     planes_asignados: int,
     *     planes_creados: int,
     *     planes_existentes: int,
     *     beneficios: int,
     *     coberturas: int,
     *     rangos: int,
     *     tarifas_creadas: int,
     *     tarifas_actualizadas: int,
     *     tarifas_sin_cambio: int,
     *     omitidas: list<string>,
     * }
     */
    public static function run(?int $whiteCompanyId = null): array
    {
        $whiteCompanyId ??= Configuration::currentWhiteCompanyId();

        $resumen = [
            'white_company_id' => $whiteCompanyId,
            'planes_asignados' => 0,
            'planes_creados' => 0,
            'planes_existentes' => 0,
            'beneficios' => 0,
            'coberturas' => 0,
            'rangos' => 0,
            'tarifas_creadas' => 0,
            'tarifas_actualizadas' => 0,
            'tarifas_sin_cambio' => 0,
            'omitidas' => [],
        ];

        if ($whiteCompanyId === null) {
            return $resumen;
        }

        $planIds = self::assignedPlanIds($whiteCompanyId);

        if ($planIds === []) {
            return $resumen;
        }

        $resumen['planes_asignados'] = count($planIds);
        $etiquetas = self::planLabels($whiteCompanyId);
        $negociadas = self::negotiatedFeesByFeeId($whiteCompanyId);

        DB::connection('mysql_vivepluss')->transaction(function () use ($planIds, $etiquetas, $negociadas, &$resumen): void {
            foreach ($planIds as $planId) {
                self::syncPlan($planId, $etiquetas, $resumen);
                self::syncBenefits($planId, $resumen);
                self::syncFeesOfPlan($planId, $negociadas, $resumen);
            }
        });

        return $resumen;
    }

    /**
     * @return list<int>
     */
    private static function assignedPlanIds(int $whiteCompanyId): array
    {
        return IntegracorpWhiteCompanyPlan::query()
            ->where('white_company_id', $whiteCompanyId)
            ->where(function ($query): void {
                $query->whereNull('status')->orWhere('status', 'ACTIVO');
            })
            ->pluck('plan_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, WhiteCompanyFee>
     */
    private static function negotiatedFeesByFeeId(int $whiteCompanyId): Collection
    {
        return WhiteCompanyFee::query()
            ->where('white_company_id', $whiteCompanyId)
            ->where(function ($query): void {
                $query->whereNull('status')->orWhere('status', 'ACTIVO');
            })
            ->get()
            ->keyBy(fn (WhiteCompanyFee $fee): int => (int) $fee->fee_id);
    }

    /**
     * @return array<int, string>
     */
    private static function planLabels(int $whiteCompanyId): array
    {
        return IntegracorpWhiteCompanyPlanLabel::query()
            ->where('white_company_id', $whiteCompanyId)
            ->pluck('display_name', 'plan_id')
            ->filter(fn (mixed $name): bool => filled($name))
            ->map(fn (mixed $name): string => (string) $name)
            ->all();
    }

    /**
     * @param  array<int, string>  $etiquetas
     * @param  array<string, mixed>  $resumen
     */
    private static function syncPlan(int $planId, array $etiquetas, array &$resumen): void
    {
        if (Plan::query()->whereKey($planId)->exists()) {
            // El nombre local manda: no se pisa lo que ViVEplus ya bautizó.
            $resumen['planes_existentes']++;

            return;
        }

        $origen = IntegracorpPlan::query()->find($planId);

        $plan = new Plan;
        $plan->id = $planId;
        $plan->business_unit_id = $origen->business_unit_id ?? 1;
        $plan->code = $origen->code ?? 'PL-'.$planId;
        $plan->description = $etiquetas[$planId] ?? ($origen->description ?? 'Plan '.$planId);
        $plan->status = 'ACTIVO';
        // Siempre BASICO: en ViVEplus ese es el tipo del catálogo que se cotiza.
        // Conservar el tipo de Integracorp (DRESS-TAILOR y demás) dejaría el plan
        // invisible en los formularios para siempre, sin que se entienda por qué.
        $plan->type = 'BASICO';
        $plan->created_by = 'sincronización Integracorp';
        $plan->save();

        $resumen['planes_creados']++;
    }

    /**
     * Los beneficios son obligatorios para asignar un plan en Integracorp, así
     * que viajan con él. Se agregan los que falten y nunca se borra ninguno: los
     * planes que ViVEplus ya tenía llevan beneficios curados a mano.
     *
     * Los ids de `benefits` **no espejan** entre las dos bases (el id 20 es
     * "VIDA" en ViVEplus y "ASISTENCIA MÉDICA POR ACCIDENTES" en Integracorp),
     * así que se resuelven por descripción.
     *
     * @param  array<string, mixed>  $resumen
     */
    private static function syncBenefits(int $planId, array &$resumen): void
    {
        $origen = IntegracorpBenefitPlan::query()->where('plan_id', $planId)->get();

        if ($origen->isEmpty()) {
            return;
        }

        $yaCargados = DB::connection('mysql_vivepluss')
            ->table('benefit_plans')
            ->where('plan_id', $planId)
            ->pluck('benefit_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        foreach ($origen as $fila) {
            $descripcion = IntegracorpBenefit::query()->where('id', $fila->benefit_id)->value('description');

            if (blank($descripcion)) {
                continue;
            }

            $benefitId = self::resolveBenefitId((string) $descripcion);

            if (in_array($benefitId, $yaCargados, true)) {
                continue;
            }

            DB::connection('mysql_vivepluss')->table('benefit_plans')->insert([
                'benefit_id' => $benefitId,
                'plan_id' => $planId,
                'description' => $fila->description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $yaCargados[] = $benefitId;
            $resumen['beneficios']++;
        }
    }

    private static function resolveBenefitId(string $descripcion): int
    {
        $existente = Benefit::query()
            ->whereRaw('LOWER(TRIM(description)) = ?', [mb_strtolower(trim($descripcion))])
            ->first();

        if ($existente !== null) {
            return (int) $existente->id;
        }

        $benefit = new Benefit;
        $benefit->code = 'BE-SYNC';
        $benefit->description = $descripcion;
        $benefit->status = 'ACTIVO';
        $benefit->created_by = 'sincronización Integracorp';
        $benefit->save();

        return (int) $benefit->id;
    }

    /**
     * Solo se traen las tarifas del plan que tengan neta pactada: sin precio de
     * venta no hay nada que cotizar.
     *
     * @param  Collection<int, WhiteCompanyFee>  $negociadas
     * @param  array<string, mixed>  $resumen
     */
    private static function syncFeesOfPlan(int $planId, Collection $negociadas, array &$resumen): void
    {
        $tarifas = IntegracorpFee::query()->where('plan_id', $planId)->get();

        foreach ($tarifas as $origen) {
            $negociada = $negociadas->get((int) $origen->id);

            if (! $negociada instanceof WhiteCompanyFee || blank($negociada->sale_price)) {
                continue;
            }

            if (filled($origen->coverage_id)) {
                self::syncCoverage((int) $origen->coverage_id, $planId, $resumen);
            }

            $ageRangeId = self::syncAgeRange((int) $origen->age_range_id, $planId, $resumen);

            if ($ageRangeId === null) {
                $resumen['omitidas'][] = sprintf(
                    'tarifa %s: no se pudo resolver su rango de edad',
                    $origen->id,
                );

                continue;
            }

            self::syncFee($origen, $negociada, $ageRangeId, $resumen);
        }
    }

    /**
     * @param  array<string, mixed>  $resumen
     */
    private static function syncCoverage(int $coverageId, int $planId, array &$resumen): void
    {
        if (Coverage::query()->whereKey($coverageId)->exists()) {
            return;
        }

        $origen = IntegracorpCoverage::query()->find($coverageId);

        if ($origen === null) {
            return;
        }

        $coverage = new Coverage;
        $coverage->id = $coverageId;
        $coverage->plan_id = $planId;
        $coverage->code = $origen->code ?? 'CO-'.$coverageId;
        $coverage->price = $origen->price;
        $coverage->status = 'ACTIVO';
        $coverage->created_by = 'sincronización Integracorp';
        $coverage->save();

        $resumen['coberturas']++;
    }

    /**
     * Devuelve el id **local** del rango de edad equivalente, creándolo si hace
     * falta.
     *
     * A diferencia de planes, coberturas y tarifas, los ids de `age_ranges` **no
     * espejan** entre las dos bases: el id 24 es "12 a 50" del plan 5 en ViVEplus
     * y "0 A 75" del plan 16 en Integracorp. Reusar el id a ciegas ataría la
     * tarifa a un rango de edad equivocado y se cotizaría mal, así que acá el id
     * de Integracorp es solo una preferencia: se usa si está libre, y si no se
     * busca un rango equivalente o se crea uno nuevo con el id que toque.
     *
     * @param  array<string, mixed>  $resumen
     */
    private static function syncAgeRange(int $ageRangeId, int $planId, array &$resumen): ?int
    {
        $origen = IntegracorpAgeRange::query()->find($ageRangeId);

        if ($origen === null) {
            return null;
        }

        $existente = AgeRange::query()->whereKey($ageRangeId)->first();

        if ($existente !== null) {
            if (self::sameAgeRange($existente, $origen, $planId)) {
                return (int) $existente->id;
            }

            // El id está ocupado por otro rango: se busca uno equivalente ya
            // cargado en el plan antes de crear un duplicado.
            $equivalente = AgeRange::query()
                ->where('plan_id', $planId)
                ->get()
                ->first(fn (AgeRange $candidato): bool => self::sameAgeRange($candidato, $origen, $planId));

            if ($equivalente !== null) {
                return (int) $equivalente->id;
            }
        }

        $ageRange = new AgeRange;

        if ($existente === null) {
            $ageRange->id = $ageRangeId;
        }

        $ageRange->plan_id = $planId;
        $ageRange->coverage_id = $origen->coverage_id;
        $ageRange->code = $origen->code ?? 'RE-'.$ageRangeId;
        $ageRange->range = (string) ($origen->range ?? '');
        $ageRange->age_init = $origen->age_init;
        $ageRange->age_end = $origen->age_end;
        $ageRange->status = 'ACTIVO';
        $ageRange->created_by = 'sincronización Integracorp';
        $ageRange->save();

        $resumen['rangos']++;

        return (int) $ageRange->id;
    }

    private static function sameAgeRange(AgeRange $local, IntegracorpAgeRange $origen, int $planId): bool
    {
        return (int) $local->plan_id === $planId
            && (int) ($local->age_init ?? -1) === (int) ($origen->age_init ?? -1)
            && (int) ($local->age_end ?? -1) === (int) ($origen->age_end ?? -1);
    }

    /**
     * @param  array<string, mixed>  $resumen
     */
    private static function syncFee(
        IntegracorpFee $origen,
        WhiteCompanyFee $negociada,
        int $ageRangeId,
        array &$resumen,
    ): void {
        $salePrice = (float) $negociada->sale_price;
        $neta = $negociada->neta !== null ? (float) $negociada->neta : null;

        $fee = Fee::query()->find((int) $origen->id);

        if ($fee === null) {
            $fee = new Fee;
            $fee->id = (int) $origen->id;
            $fee->code = $origen->code ?? 'FA-'.$origen->id;
            $fee->created_by = 'sincronización Integracorp';
            $nuevo = true;
        } else {
            // El id de la tarifa sí espeja entre las dos bases y es la clave que
            // usa `white_company_fees.fee_id`, pero si el id local resultara ser
            // otra tarifa distinta se estaría pisando un precio ajeno: se informa
            // en vez de sobrescribirlo.
            $mismaTarifa = (int) $fee->plan_id === (int) $origen->plan_id
                && (int) ($fee->coverage_id ?? 0) === (int) ($origen->coverage_id ?? 0);

            if (! $mismaTarifa) {
                $resumen['omitidas'][] = sprintf(
                    'tarifa %d: en ViVEplus ese id es otra tarifa (plan %s, cobertura %s)',
                    $origen->id,
                    $fee->plan_id ?? '—',
                    $fee->coverage_id ?? '—',
                );

                return;
            }

            $nuevo = false;
            $sinCambio = (float) $fee->price === $salePrice
                && (int) $fee->age_range_id === $ageRangeId;

            if ($sinCambio) {
                $resumen['tarifas_sin_cambio']++;

                return;
            }
        }

        $fee->plan_id = (int) $origen->plan_id;
        $fee->age_range_id = $ageRangeId;
        $fee->coverage_id = $origen->coverage_id;
        $fee->price = $salePrice;
        $fee->neta = $neta;
        $fee->range = $origen->range;
        $fee->coverage = $origen->coverage;
        $fee->status = 'ACTIVO';
        $fee->save();

        $resumen[$nuevo ? 'tarifas_creadas' : 'tarifas_actualizadas']++;
    }
}
