<?php

use App\Models\Agent;
use App\Models\CorporateQuote;
use App\Models\IndividualQuote;

uses(Tests\TestCase::class);

it('no hereda mysql_vivepluss al resolver el agente de una cotización individual', function () {
    $quote = new IndividualQuote;
    $quote->setRawAttributes(['agent_id' => 528], true);

    expect($quote->agent()->getRelated()->getConnectionName())
        ->toBe(config('database.default'))
        ->not->toBe('mysql_vivepluss');
});

it('no hereda mysql_vivepluss al resolver el agente de una cotización corporativa', function () {
    $quote = new CorporateQuote;
    $quote->setRawAttributes(['agent_id' => 528], true);

    expect($quote->agent()->getRelated()->getConnectionName())
        ->toBe(config('database.default'))
        ->not->toBe('mysql_vivepluss');
});

it('consulta agents por la conexión por defecto y no por mysql_vivepluss', function () {
    expect((new Agent)->getConnectionName())
        ->toBe(config('database.default'))
        ->not->toBe('mysql_vivepluss');
});
