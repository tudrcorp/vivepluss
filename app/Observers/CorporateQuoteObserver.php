<?php

namespace App\Observers;

use App\Models\CorporateQuote;

/**
 * Ver App\Observers\IndividualQuoteObserver: mismo mecanismo, código a
 * partir del id real ya asignado por MySQL (atómico), no de un max(id)+1
 * precalculado en el formulario.
 */
class CorporateQuoteObserver
{
    public function created(CorporateQuote $quote): void
    {
        $quote->updateQuietly([
            'code' => 'VP-CC-'.str_pad((string) ($quote->id - CorporateQuote::ID_OFFSET), 4, '0', STR_PAD_LEFT),
        ]);
    }
}
