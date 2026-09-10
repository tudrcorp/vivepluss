<?php

use App\Models\IndividualQuote;
use App\Support\IndividualQuoteResolver;

it('treats ids below the offset as legacy Integracorp quotes', function () {
    expect(IndividualQuoteResolver::isLegacy(1))->toBeTrue()
        ->and(IndividualQuoteResolver::isLegacy(IndividualQuote::ID_OFFSET - 1))->toBeTrue();
});

it('treats ids at or above the offset as ViVEplus quotes', function () {
    expect(IndividualQuoteResolver::isLegacy(IndividualQuote::ID_OFFSET))->toBeFalse()
        ->and(IndividualQuoteResolver::isLegacy(IndividualQuote::ID_OFFSET + 1))->toBeFalse();
});
