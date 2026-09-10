<?php

declare(strict_types=1);

use App\Models\IntegracorpAgeRange;
use App\Models\IntegracorpFee;
use App\Support\WhiteCompanies\WhiteCompanyNegotiatedRateResolver;

/**
 * El plan de una tarifa sale de `fees.plan_id` (catálogo de Integracorp, columna
 * canónica desde 2026-08-18) y no de `age_ranges.plan_id`. Importa acá más que
 * en ningún otro lado: este resolver fija el monto de la venta y la comisión que
 * se escriben en Integracorp al aprobar un pago CREDITO.
 *
 * Tests puros en memoria: no tocan ninguna conexión.
 */
function feeConPlan(?int $planId, int $ageInit, int $ageEnd): IntegracorpFee
{
    $ageRange = new IntegracorpAgeRange;
    $ageRange->age_init = $ageInit;
    $ageRange->age_end = $ageEnd;

    $fee = new IntegracorpFee;
    $fee->plan_id = $planId;
    $fee->setRelation('ageRange', $ageRange);

    return $fee;
}

function coincideConPlan(IntegracorpFee $fee, int $planId, int $edad): bool
{
    $resolver = new WhiteCompanyNegotiatedRateResolver;
    $method = new ReflectionMethod($resolver, 'feeMatchesAge');
    $method->setAccessible(true);

    return (bool) $method->invoke($resolver, $edad, $fee, $planId);
}

it('descarta una tarifa sin plan_id aunque la edad encaje', function (): void {
    $fee = feeConPlan(null, 0, 45);

    expect(coincideConPlan($fee, 2, 30))->toBeFalse()
        ->and(coincideConPlan($fee, 3, 30))->toBeFalse();
});

it('descarta una tarifa de otro plan aunque la edad encaje', function (): void {
    $fee = feeConPlan(3, 0, 45);

    expect(coincideConPlan($fee, 2, 30))->toBeFalse();
});

it('acepta la tarifa de su plan cuando la edad está en rango', function (): void {
    $fee = feeConPlan(2, 0, 45);

    expect(coincideConPlan($fee, 2, 30))->toBeTrue()
        ->and(coincideConPlan($fee, 2, 0))->toBeTrue()
        ->and(coincideConPlan($fee, 2, 45))->toBeTrue();
});

it('descarta la tarifa de su plan cuando la edad queda fuera de rango', function (): void {
    $fee = feeConPlan(2, 0, 45);

    expect(coincideConPlan($fee, 2, 46))->toBeFalse();
});

it('exige plan también para el plan inicial', function (): void {
    // Antes el plan inicial se daba por bueno sin comprobar el plan de la tarifa.
    $planInicial = 1;
    $sinPlan = feeConPlan(null, 0, 99);
    $conPlanInicial = feeConPlan($planInicial, 0, 99);

    expect(coincideConPlan($sinPlan, $planInicial, 35))->toBeFalse()
        ->and(coincideConPlan($conPlanInicial, $planInicial, 35))->toBeTrue();
});

it('filtra el plan en SQL en vez de descartarlo en PHP', function (): void {
    $source = file_get_contents(dirname(__DIR__, 2).'/app/Support/WhiteCompanies/WhiteCompanyNegotiatedRateResolver.php');

    expect($source)->toContain("->where('plan_id', \$planId)")
        ->and($source)->not->toContain('(int) $ageRange->plan_id === $planId');
});
