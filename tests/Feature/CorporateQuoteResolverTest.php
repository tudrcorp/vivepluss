<?php

use App\Models\CorporateQuote;
use App\Models\IntegracorpCorporateQuote;
use App\Support\CorporateQuoteResolver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ver tests/Feature/IndividualQuoteResolverTest.php: `corporate_quotes` de
 * Integracorp tampoco tiene migración en este repo, así que se crea una
 * versión mínima solo para estos tests en la conexión por defecto (sqlite
 * :memory: en testing) -RefreshDatabase la revierte junto con el resto.
 *
 * La tabla nueva de ViVEplus vive en mysql_vivepluss (conexión real, fuera
 * del sqlite :memory: de testing), así que las filas que crean estos tests
 * se borran explícitamente al final para no ensuciar la base local.
 */
beforeEach(function () {
    Schema::create('corporate_quotes', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->string('full_name')->nullable();
        $table->string('status')->default('ACTIVA');
        $table->timestamps();
    });
});

afterEach(function () {
    CorporateQuote::where('id', '>=', CorporateQuote::ID_OFFSET)->delete();
});

it('generates sequential atomic codes derived from the real auto-increment id', function () {
    $first = CorporateQuote::create(['full_name' => 'Empresa Uno', 'status' => 'ACTIVA']);
    $second = CorporateQuote::create(['full_name' => 'Empresa Dos', 'status' => 'ACTIVA']);

    expect($first->id)->toBeGreaterThanOrEqual(CorporateQuote::ID_OFFSET)
        ->and($second->id)->toBe($first->id + 1)
        ->and($first->code)->toBe('VP-CC-'.str_pad((string) ($first->id - CorporateQuote::ID_OFFSET), 4, '0', STR_PAD_LEFT))
        ->and($second->code)->toBe('VP-CC-'.str_pad((string) ($second->id - CorporateQuote::ID_OFFSET), 4, '0', STR_PAD_LEFT))
        ->and($first->code)->not->toBe($second->code);
});

it('resolves a legacy id to the Integracorp model and a new id to the ViVEplus model', function () {
    $legacy = IntegracorpCorporateQuote::create(['code' => 'COT-CORP-0009999', 'full_name' => 'Legacy Empresa', 'status' => 'APROBADA']);
    $new = CorporateQuote::create(['full_name' => 'Empresa ViVEplus', 'status' => 'APROBADA']);

    expect(CorporateQuoteResolver::find($legacy->id))
        ->toBeInstanceOf(IntegracorpCorporateQuote::class)
        ->and(CorporateQuoteResolver::find($new->id))
        ->toBeInstanceOf(CorporateQuote::class)
        ->and(CorporateQuoteResolver::find(null))->toBeNull();
});
