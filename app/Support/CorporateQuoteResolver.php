<?php

namespace App\Support;

use App\Models\CorporateQuote;
use App\Models\IntegracorpCorporateQuote;

/**
 * Ver App\Support\IndividualQuoteResolver: mismo mecanismo (decide legacy vs.
 * nuevo por rango de id), pero sin coverageOptions()/detailSum() -a
 * diferencia de individual, no existe hoy ningún selector vivo en el panel
 * que necesite mezclar cotizaciones corporativas legacy y nuevas en una
 * misma lista. Se usa solo para lectura (AffiliationCorporate::corporate_quote),
 * nunca para escribir.
 */
class CorporateQuoteResolver
{
    public static function isLegacy(int $corporateQuoteId): bool
    {
        return $corporateQuoteId < CorporateQuote::ID_OFFSET;
    }

    public static function find(?int $corporateQuoteId): CorporateQuote|IntegracorpCorporateQuote|null
    {
        if (blank($corporateQuoteId)) {
            return null;
        }

        return static::isLegacy($corporateQuoteId)
            ? IntegracorpCorporateQuote::find($corporateQuoteId)
            : CorporateQuote::find($corporateQuoteId);
    }
}
