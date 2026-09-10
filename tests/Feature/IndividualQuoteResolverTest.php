<?php

use App\Models\IndividualQuote;
use App\Models\IntegracorpIndividualQuote;
use App\Support\IndividualQuoteResolver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `individual_quotes` de Integracorp no tiene migración en este repo (vive
 * en el volcado de BD de cada entorno, igual que `affiliations` en
 * AffiliationDocumentWebhookTest), así que se crea una versión mínima solo
 * para estos tests en la conexión por defecto (sqlite :memory: en testing,
 * ver phpunit.xml) -RefreshDatabase la revierte junto con el resto.
 *
 * La tabla nueva de ViVEplus sí vive en mysql_vivepluss (conexión real,
 * fuera del sqlite :memory: de testing), así que las filas que crean estos
 * tests se borran explícitamente al final para no ensuciar la base local.
 */
beforeEach(function () {
    Schema::create('individual_quotes', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->string('full_name')->nullable();
        $table->string('status')->default('ACTIVA');
        $table->timestamps();
    });
});

afterEach(function () {
    IndividualQuote::where('id', '>=', IndividualQuote::ID_OFFSET)->delete();
});

it('generates sequential atomic codes derived from the real auto-increment id', function () {
    $first = IndividualQuote::create(['full_name' => 'Cliente Uno', 'status' => 'ACTIVA']);
    $second = IndividualQuote::create(['full_name' => 'Cliente Dos', 'status' => 'ACTIVA']);

    expect($first->id)->toBeGreaterThanOrEqual(IndividualQuote::ID_OFFSET)
        ->and($second->id)->toBe($first->id + 1)
        ->and($first->code)->toBe('VP-CI-'.str_pad((string) ($first->id - IndividualQuote::ID_OFFSET), 4, '0', STR_PAD_LEFT))
        ->and($second->code)->toBe('VP-CI-'.str_pad((string) ($second->id - IndividualQuote::ID_OFFSET), 4, '0', STR_PAD_LEFT))
        ->and($first->code)->not->toBe($second->code);
});

it('resolves a legacy id to the Integracorp model and a new id to the ViVEplus model', function () {
    $legacy = IntegracorpIndividualQuote::create(['code' => 'COT-IND-0009999', 'full_name' => 'Legacy Cliente', 'status' => 'APROBADA']);
    $new = IndividualQuote::create(['full_name' => 'Cliente ViVEplus', 'status' => 'APROBADA']);

    expect(IndividualQuoteResolver::find($legacy->id))
        ->toBeInstanceOf(IntegracorpIndividualQuote::class)
        ->and(IndividualQuoteResolver::find($new->id))
        ->toBeInstanceOf(IndividualQuote::class)
        ->and(IndividualQuoteResolver::find(null))->toBeNull();
});
