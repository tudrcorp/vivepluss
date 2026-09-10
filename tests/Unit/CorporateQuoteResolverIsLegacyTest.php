<?php

use App\Models\CorporateQuote;
use App\Support\CorporateQuoteResolver;

it('treats ids below the offset as legacy Integracorp quotes', function () {
    expect(CorporateQuoteResolver::isLegacy(1))->toBeTrue()
        ->and(CorporateQuoteResolver::isLegacy(CorporateQuote::ID_OFFSET - 1))->toBeTrue();
});

it('treats ids at or above the offset as ViVEplus quotes', function () {
    expect(CorporateQuoteResolver::isLegacy(CorporateQuote::ID_OFFSET))->toBeFalse()
        ->and(CorporateQuoteResolver::isLegacy(CorporateQuote::ID_OFFSET + 1))->toBeFalse();
});
