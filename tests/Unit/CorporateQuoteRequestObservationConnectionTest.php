<?php

use App\Models\CorporateQuoteRequestObservation;

it('stores corporate quote request observations on mysql_vivepluss', function () {
    expect((new CorporateQuoteRequestObservation)->getConnectionName())
        ->toBe('mysql_vivepluss');
});
