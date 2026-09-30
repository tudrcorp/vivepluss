<?php

use App\Models\Sale;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

/**
 * sales vive en el volcado de Integracorp (conexión default). Se arma una tabla
 * mínima en sqlite para no tocar MySQL.
 */
beforeEach(function (): void {
    Schema::create('sales', function (Blueprint $table) {
        $table->id();
        $table->string('affiliation_code')->nullable();
        $table->string('payment_method')->nullable();
        $table->decimal('total_amount', 14, 2)->default(0);
        $table->timestamps();
    });
});

afterEach(function (): void {
    Schema::dropIfExists('sales');
});

it('rechaza crear una venta con pago a CREDITO', function (string $metodo): void {
    expect(fn () => Sale::create([
        'affiliation_code' => 'TDEC-IND-000431',
        'payment_method' => $metodo,
        'total_amount' => 42,
    ]))->toThrow(RuntimeException::class);

    expect(Sale::count())->toBe(0);
})->with(['CREDITO', 'credito', ' CREDITO ']);

it('rechaza cambiar una venta existente a CREDITO', function (): void {
    $sale = Sale::create(['affiliation_code' => 'X', 'payment_method' => 'ZELLE', 'total_amount' => 10]);

    expect(fn () => $sale->update(['payment_method' => 'CREDITO']))->toThrow(RuntimeException::class);
    expect($sale->fresh()->payment_method)->toBe('ZELLE');
});

it('permite ventas con métodos de pago reales', function (string $metodo): void {
    Sale::create(['affiliation_code' => 'X', 'payment_method' => $metodo, 'total_amount' => 10]);

    expect(Sale::count())->toBe(1);
})->with(['ZELLE', 'TRANSFERENCIA', 'PAGO MOVIL']);

it('el flujo de pago a crédito ya no registra venta ni comisión', function (): void {
    $source = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/AffiliationController.php');

    expect($source)
        ->not->toContain('Sale::create')
        ->not->toContain('registerSaleAndCommission')
        ->not->toContain('storeCommission');
});
