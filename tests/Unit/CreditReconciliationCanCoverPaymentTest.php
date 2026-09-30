<?php

use App\Models\CreditReconciliation;
use App\Models\WhiteCompany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

/**
 * white_companies / credit_reconciliations viven en el volcado de Integracorp
 * (conexión default). Se arman tablas mínimas en sqlite para no tocar MySQL.
 */
beforeEach(function (): void {
    Schema::create('white_companies', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->decimal('assigned_credit', 14, 2)->default(0);
        $table->timestamps();
    });

    Schema::create('credit_reconciliations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('white_company_id')->nullable();
        $table->decimal('total_to_pay', 14, 2)->default(0);
        $table->timestamps();
    });
});

afterEach(function (): void {
    Schema::dropIfExists('credit_reconciliations');
    Schema::dropIfExists('white_companies');
});

it('permite el pago a crédito cuando Integracorp no asignó cupo', function (): void {
    $whiteCompany = WhiteCompany::query()->create([
        'name' => 'Vive Plus',
        'assigned_credit' => 0,
    ]);

    CreditReconciliation::query()->create([
        'white_company_id' => $whiteCompany->id,
        'total_to_pay' => 277.75,
    ]);

    expect(CreditReconciliation::canCoverPayment($whiteCompany->id, 45.00))->toBeTrue()
        ->and(CreditReconciliation::canCoverPayment($whiteCompany->id, 1000.00))->toBeTrue();
});

it('permite el pago a crédito si el cupo asignado es cero aunque no haya movimientos', function (): void {
    $whiteCompany = WhiteCompany::query()->create([
        'name' => 'Vive Plus',
        'assigned_credit' => 0,
    ]);

    expect(CreditReconciliation::canCoverPayment($whiteCompany->id, 180.00))->toBeTrue();
});

it('bloquea el pago cuando hay cupo y el monto supera el saldo restante', function (): void {
    $whiteCompany = WhiteCompany::query()->create([
        'name' => 'Vive Plus',
        'assigned_credit' => 1000,
    ]);

    CreditReconciliation::query()->create([
        'white_company_id' => $whiteCompany->id,
        'total_to_pay' => 950,
    ]);

    expect(CreditReconciliation::canCoverPayment($whiteCompany->id, 100.00))->toBeFalse()
        ->and(CreditReconciliation::canCoverPayment($whiteCompany->id, 50.00))->toBeTrue();
});

it('permite el pago cuando hay cupo y el monto cabe en el saldo restante', function (): void {
    $whiteCompany = WhiteCompany::query()->create([
        'name' => 'Vive Plus',
        'assigned_credit' => 500,
    ]);

    expect(CreditReconciliation::canCoverPayment($whiteCompany->id, 500.00))->toBeTrue()
        ->and(CreditReconciliation::canCoverPayment($whiteCompany->id, 500.01))->toBeFalse();
});
