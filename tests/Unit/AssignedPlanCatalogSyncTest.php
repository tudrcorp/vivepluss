<?php

declare(strict_types=1);

use App\Models\AgeRange;
use App\Models\Benefit;
use App\Models\Fee;
use App\Models\Plan;
use App\Support\Catalog\AssignedPlanCatalogSync;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sincronización hacia ViVEplus de los planes que Integracorp le asigna en la
 * matriz de negociación.
 *
 * El catálogo de Integracorp (`plans`, `coverages`, `age_ranges`, `fees`,
 * `white_company_fees`) vive en la conexión por defecto, que en tests es sqlite
 * en memoria: se arma acá el esquema mínimo, porque esas tablas no tienen
 * migración en este repo. El catálogo propio de ViVEplus está pinneado a
 * `mysql_vivepluss`, que **sí es la base real**, así que todo lo que escriba ahí
 * va dentro de una transacción que se revierte.
 *
 * Vive en tests/Unit a propósito: `RefreshDatabase` (que Pest aplica solo a
 * tests/Feature) ejecuta también las migraciones pinneadas a `mysql_vivepluss`
 * contra la base real de ViVEplus, cosa que este test no necesita y no conviene
 * disparar.
 */
uses(Tests\TestCase::class);
const EMPRESA_ALIADA = 9901;

/**
 * Los ids se generan en un rango alto a propósito: el catálogo real de ViVEplus
 * ya ocupa los bajos y los tests escriben sobre esa misma base.
 */
const ID_BASE_PRUEBA = 900000;

beforeEach(function (): void {
    Schema::create('plans', function ($table): void {
        $table->id();
        $table->unsignedBigInteger('business_unit_id')->nullable();
        $table->string('code')->nullable();
        $table->string('description')->nullable();
        $table->string('status')->nullable();
        $table->string('type')->nullable();
        $table->string('created_by')->nullable();
        $table->timestamps();
    });

    Schema::create('coverages', function ($table): void {
        $table->id();
        $table->unsignedBigInteger('plan_id')->nullable();
        $table->string('code')->nullable();
        $table->decimal('price', 10, 2)->nullable();
        $table->string('status')->nullable();
        $table->string('created_by')->nullable();
        $table->timestamps();
    });

    Schema::create('age_ranges', function ($table): void {
        $table->id();
        $table->unsignedBigInteger('plan_id')->nullable();
        $table->unsignedBigInteger('coverage_id')->nullable();
        $table->string('code')->nullable();
        $table->string('range')->nullable();
        $table->integer('age_init')->nullable();
        $table->integer('age_end')->nullable();
        $table->string('status')->nullable();
        $table->string('created_by')->nullable();
        $table->timestamps();
    });

    Schema::create('fees', function ($table): void {
        $table->id();
        $table->string('code')->nullable();
        $table->unsignedBigInteger('plan_id')->nullable();
        $table->unsignedBigInteger('age_range_id')->nullable();
        $table->unsignedBigInteger('coverage_id')->nullable();
        $table->decimal('price', 10, 2)->nullable();
        $table->string('status')->nullable();
        $table->string('range')->nullable();
        $table->string('coverage')->nullable();
        $table->string('created_by')->nullable();
        $table->timestamps();
    });

    Schema::create('white_company_fees', function ($table): void {
        $table->id();
        $table->unsignedBigInteger('white_company_id');
        $table->unsignedBigInteger('fee_id');
        $table->decimal('sale_price', 10, 2)->nullable();
        $table->decimal('neta', 10, 2)->nullable();
        $table->string('status')->nullable();
        $table->string('created_by')->nullable();
        $table->timestamps();
    });

    Schema::create('white_company_plans', function ($table): void {
        $table->id();
        $table->unsignedBigInteger('white_company_id');
        $table->unsignedBigInteger('plan_id');
        $table->string('status')->nullable();
        $table->string('created_by')->nullable();
        $table->timestamps();
    });

    Schema::create('benefits', function ($table): void {
        $table->id();
        $table->string('code')->nullable();
        $table->string('description')->nullable();
        $table->string('status')->nullable();
        $table->string('created_by')->nullable();
        $table->timestamps();
    });

    Schema::create('benefit_plans', function ($table): void {
        $table->id();
        $table->unsignedBigInteger('benefit_id');
        $table->unsignedBigInteger('plan_id');
        $table->string('description')->nullable();
        $table->timestamps();
    });

    Schema::create('white_company_plan_labels', function ($table): void {
        $table->id();
        $table->unsignedBigInteger('white_company_id');
        $table->unsignedBigInteger('plan_id');
        $table->string('display_name')->nullable();
        $table->string('short_label')->nullable();
        $table->timestamps();
    });

    DB::connection('mysql_vivepluss')->beginTransaction();
});

afterEach(function (): void {
    DB::connection('mysql_vivepluss')->rollBack();
});

/**
 * Arma en el catálogo de Integracorp un plan con una tarifa y la deja pactada
 * para la empresa aliada. Devuelve [planId, feeId].
 *
 * @return array{0: int, 1: int}
 */
function asignarPlanEnIntegracorp(
    string $descripcion,
    float $precioLista,
    float $precioVenta,
    int $ageInit = 0,
    int $ageEnd = 45,
    bool $conNeta = true,
): array {
    $siguiente = ID_BASE_PRUEBA + DB::table('plans')->count() * 10;

    $planId = $siguiente + 1;
    $coverageId = $siguiente + 2;
    $ageRangeId = $siguiente + 3;
    $feeId = $siguiente + 4;

    DB::table('plans')->insert([
        'id' => $planId,
        'business_unit_id' => 1,
        'code' => 'PL-TEST',
        'description' => $descripcion,
        'status' => 'ACTIVO',
        'type' => 'DRESS-TAILOR',
    ]);

    DB::table('coverages')->insert([
        'id' => $coverageId,
        'plan_id' => $planId,
        'code' => 'CO-TEST',
        'price' => 1000,
        'status' => 'ACTIVO',
    ]);

    DB::table('age_ranges')->insert([
        'id' => $ageRangeId,
        'plan_id' => $planId,
        'code' => 'RE-TEST',
        'range' => "{$ageInit} a {$ageEnd}",
        'age_init' => $ageInit,
        'age_end' => $ageEnd,
        'status' => 'ACTIVO',
    ]);

    DB::table('fees')->insert([
        'id' => $feeId,
        'code' => 'FA-TEST',
        'plan_id' => $planId,
        'age_range_id' => $ageRangeId,
        'coverage_id' => $coverageId,
        'price' => $precioLista,
        'status' => 'ACTIVO',
    ]);

    // Todo plan asignable necesita beneficios: es requisito en Integracorp.
    $benefitId = DB::table('benefits')->insertGetId([
        'code' => 'BE-TEST',
        'description' => 'BENEFICIO DE PRUEBA '.$planId,
        'status' => 'ACTIVO',
    ]);

    DB::table('benefit_plans')->insert([
        'benefit_id' => $benefitId,
        'plan_id' => $planId,
        'description' => 'detalle',
    ]);

    // Paso 1: el analista habilita el plan para la aliada.
    DB::table('white_company_plans')->insert([
        'white_company_id' => EMPRESA_ALIADA,
        'plan_id' => $planId,
        'status' => 'ACTIVO',
    ]);

    // Paso 2: pacta venta y neta de la tarifa en la matriz de negociación.
    if ($conNeta) {
        DB::table('white_company_fees')->insert([
            'white_company_id' => EMPRESA_ALIADA,
            'fee_id' => $feeId,
            'sale_price' => $precioVenta,
            'neta' => $precioLista,
            'status' => 'ACTIVO',
        ]);
    }

    return [$planId, $feeId];
}

it('trae el plan asignado al catálogo de ViVEplus con el precio de venta pactado', function (): void {
    [$planId, $feeId] = asignarPlanEnIntegracorp('PLAN DE PRUEBA', precioLista: 100, precioVenta: 130);

    $resumen = AssignedPlanCatalogSync::run(EMPRESA_ALIADA);

    expect($resumen['planes_creados'])->toBe(1)
        ->and($resumen['tarifas_creadas'])->toBe(1);

    $plan = Plan::query()->find($planId);
    $fee = Fee::query()->find($feeId);

    expect($plan)->not->toBeNull()
        ->and($fee)->not->toBeNull()
        // Lo que ViVEplus cobra es la venta pactada, no el precio de lista.
        ->and((float) $fee->price)->toBe(130.0)
        ->and((float) $fee->neta)->toBe(100.0)
        ->and((int) $fee->plan_id)->toBe($planId);
});

it('deja el plan como BASICO para que pueda ofrecerse en las cotizaciones', function (): void {
    [$planId] = asignarPlanEnIntegracorp('PLAN A MEDIDA', precioLista: 100, precioVenta: 130);

    AssignedPlanCatalogSync::run(EMPRESA_ALIADA);

    // En Integracorp el plan es DRESS-TAILOR; conservarlo lo dejaría invisible.
    expect(Plan::query()->find($planId)->type)->toBe('BASICO');
});

it('usa el nombre comercial que Integracorp definió para la empresa aliada', function (): void {
    [$planId] = asignarPlanEnIntegracorp('NOMBRE INTERNO', precioLista: 100, precioVenta: 130);

    DB::table('white_company_plan_labels')->insert([
        'white_company_id' => EMPRESA_ALIADA,
        'plan_id' => $planId,
        'display_name' => 'PLAN COMERCIAL',
    ]);

    AssignedPlanCatalogSync::run(EMPRESA_ALIADA);

    expect(Plan::query()->find($planId)->description)->toBe('PLAN COMERCIAL');
});

it('nunca pisa el nombre de un plan que ViVEplus ya bautizó', function (): void {
    [$planId] = asignarPlanEnIntegracorp('NOMBRE DE INTEGRACORP', precioLista: 100, precioVenta: 130);

    $plan = new Plan;
    $plan->id = $planId;
    $plan->business_unit_id = 1;
    $plan->code = 'LOCAL';
    $plan->description = 'NOMBRE PROPIO DE VIVEPLUS';
    $plan->status = 'ACTIVO';
    $plan->type = 'BASICO';
    $plan->created_by = 'test';
    $plan->save();

    $resumen = AssignedPlanCatalogSync::run(EMPRESA_ALIADA);

    expect($resumen['planes_creados'])->toBe(0)
        ->and($resumen['planes_existentes'])->toBe(1)
        ->and(Plan::query()->find($planId)->description)->toBe('NOMBRE PROPIO DE VIVEPLUS');
});

it('trae el plan asignado sin netas pero no lo ofrece para cotizar', function (): void {
    // Asignar habilita el plan; recién las netas lo hacen cotizable.
    [$planId] = asignarPlanEnIntegracorp('PLAN SIN NETAS', precioLista: 100, precioVenta: 130, conNeta: false);

    $resumen = AssignedPlanCatalogSync::run(EMPRESA_ALIADA);

    expect($resumen['planes_asignados'])->toBe(1)
        ->and($resumen['tarifas_creadas'])->toBe(0)
        ->and(Plan::query()->find($planId))->not->toBeNull()
        ->and(Plan::cotizable()->whereKey($planId)->exists())->toBeFalse();
});

it('sincroniza los beneficios del plan asignado', function (): void {
    [$planId] = asignarPlanEnIntegracorp('PLAN CON BENEFICIOS', precioLista: 100, precioVenta: 130);

    $resumen = AssignedPlanCatalogSync::run(EMPRESA_ALIADA);

    expect($resumen['beneficios'])->toBeGreaterThan(0)
        ->and(DB::connection('mysql_vivepluss')->table('benefit_plans')->where('plan_id', $planId)->count())
        ->toBeGreaterThan(0);
});

it('reutiliza el beneficio local con la misma descripción en vez de duplicarlo', function (): void {
    // Los ids de `benefits` no espejan entre las dos bases, así que se resuelven
    // por descripción.
    [$planId] = asignarPlanEnIntegracorp('PLAN BENEFICIO REPETIDO', precioLista: 100, precioVenta: 130);

    $descripcion = (string) DB::table('benefits')->value('description');

    $existente = new Benefit;
    $existente->code = 'LOCAL';
    $existente->description = $descripcion;
    $existente->status = 'ACTIVO';
    $existente->created_by = 'test';
    $existente->save();

    AssignedPlanCatalogSync::run(EMPRESA_ALIADA);

    $usados = DB::connection('mysql_vivepluss')
        ->table('benefit_plans')
        ->where('plan_id', $planId)
        ->pluck('benefit_id');

    expect($usados)->toContain((int) $existente->id)
        ->and(Benefit::query()->where('description', $descripcion)->count())->toBe(1);
});

it('no vuelve a escribir cuando ya está sincronizado', function (): void {
    asignarPlanEnIntegracorp('PLAN IDEMPOTENTE', precioLista: 100, precioVenta: 130);

    AssignedPlanCatalogSync::run(EMPRESA_ALIADA);
    $segunda = AssignedPlanCatalogSync::run(EMPRESA_ALIADA);

    expect($segunda['tarifas_creadas'])->toBe(0)
        ->and($segunda['tarifas_actualizadas'])->toBe(0)
        ->and($segunda['tarifas_sin_cambio'])->toBe(1);
});

it('propaga a ViVEplus un cambio de precio negociado', function (): void {
    [, $feeId] = asignarPlanEnIntegracorp('PLAN REPRECIADO', precioLista: 100, precioVenta: 130);

    AssignedPlanCatalogSync::run(EMPRESA_ALIADA);

    DB::table('white_company_fees')->where('fee_id', $feeId)->update(['sale_price' => 175]);

    $resumen = AssignedPlanCatalogSync::run(EMPRESA_ALIADA);

    expect($resumen['tarifas_actualizadas'])->toBe(1)
        ->and((float) Fee::query()->find($feeId)->price)->toBe(175.0);
});

it('no sincroniza un plan que no está asignado, aunque tenga netas pactadas', function (): void {
    [$planId, $feeId] = asignarPlanEnIntegracorp('PLAN NO ASIGNADO', precioLista: 100, precioVenta: 130);

    // La neta queda, pero se retira la habilitación del plan.
    DB::table('white_company_plans')->where('plan_id', $planId)->delete();

    $resumen = AssignedPlanCatalogSync::run(EMPRESA_ALIADA);

    expect($resumen['planes_asignados'])->toBe(0)
        ->and($resumen['tarifas_creadas'])->toBe(0)
        ->and(Plan::query()->find($planId))->toBeNull()
        ->and(Fee::query()->find($feeId))->toBeNull();
});

it('ignora los planes asignados a otra empresa aliada', function (): void {
    [$planId] = asignarPlanEnIntegracorp('PLAN DE OTRA ALIADA', precioLista: 100, precioVenta: 130);

    DB::table('white_company_plans')->where('plan_id', $planId)->update(['white_company_id' => 9999]);

    $resumen = AssignedPlanCatalogSync::run(EMPRESA_ALIADA);

    expect($resumen['planes_asignados'])->toBe(0)
        ->and(Plan::query()->find($planId))->toBeNull();
});

it('deja fuera las tarifas del plan que todavía no tienen neta pactada', function (): void {
    [$planId, $feeId] = asignarPlanEnIntegracorp('PLAN NETA PARCIAL', precioLista: 100, precioVenta: 130);

    // Una segunda tarifa del mismo plan, sin neta.
    $sinNeta = DB::table('fees')->insertGetId([
        'code' => 'FA-SIN-NETA',
        'plan_id' => $planId,
        'age_range_id' => DB::table('fees')->where('id', $feeId)->value('age_range_id'),
        'coverage_id' => DB::table('fees')->where('id', $feeId)->value('coverage_id'),
        'price' => 999,
        'status' => 'ACTIVO',
    ]);

    $resumen = AssignedPlanCatalogSync::run(EMPRESA_ALIADA);

    expect($resumen['tarifas_creadas'])->toBe(1)
        ->and(Fee::query()->find($feeId))->not->toBeNull()
        ->and(Fee::query()->find($sinNeta))->toBeNull();
});

it('no ata la tarifa a un rango de edad local que signifique otra cosa', function (): void {
    // Los ids de age_ranges no espejan entre las dos bases: si el id que trae
    // Integracorp ya existe en ViVEplus con otras edades, hay que resolver otro.
    [$planId, $feeId] = asignarPlanEnIntegracorp('PLAN CON CHOQUE', precioLista: 100, precioVenta: 130, ageInit: 0, ageEnd: 45);

    $idDeIntegracorp = (int) DB::table('fees')->where('id', $feeId)->value('age_range_id');

    $ocupado = new AgeRange;
    $ocupado->id = $idDeIntegracorp;
    $ocupado->plan_id = 999_999;
    $ocupado->code = 'OCUPADO';
    $ocupado->range = '80 a 90';
    $ocupado->age_init = 80;
    $ocupado->age_end = 90;
    $ocupado->status = 'ACTIVO';
    $ocupado->created_by = 'test';
    $ocupado->save();

    AssignedPlanCatalogSync::run(EMPRESA_ALIADA);

    $fee = Fee::query()->find($feeId);
    $rango = AgeRange::query()->find($fee->age_range_id);

    expect($rango->age_init)->toBe(0)
        ->and($rango->age_end)->toBe(45)
        ->and((int) $rango->plan_id)->toBe($planId)
        // El rango ajeno quedó intacto.
        ->and((int) AgeRange::query()->find($idDeIntegracorp)->age_init)->toBe(80);
});

it('no pisa una tarifa local que en ViVEplus es otra tarifa distinta', function (): void {
    [, $feeId] = asignarPlanEnIntegracorp('PLAN COLISION', precioLista: 100, precioVenta: 130);

    $ajena = new Fee;
    $ajena->id = $feeId;
    $ajena->code = 'AJENA';
    $ajena->plan_id = 888_888;
    $ajena->age_range_id = ID_BASE_PRUEBA + 1;
    $ajena->coverage_id = 777_777;
    $ajena->price = 55;
    $ajena->status = 'ACTIVO';
    $ajena->created_by = 'test';
    $ajena->save();

    $resumen = AssignedPlanCatalogSync::run(EMPRESA_ALIADA);

    expect($resumen['omitidas'])->not->toBeEmpty()
        ->and((float) Fee::query()->find($feeId)->price)->toBe(55.0);
});
