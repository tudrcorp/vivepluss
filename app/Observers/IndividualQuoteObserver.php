<?php

namespace App\Observers;

use App\Models\IndividualQuote;

/**
 * Genera el código legible a partir del id real que MySQL ya asignó
 * (atómico por diseño), en vez del `max(id)+1` que se calculaba antes en el
 * formulario -condición de carrera entre dos creaciones simultáneas.
 */
class IndividualQuoteObserver
{
    public function created(IndividualQuote $quote): void
    {
        $quote->updateQuietly([
            'code' => 'VP-CI-'.str_pad((string) ($quote->id - IndividualQuote::ID_OFFSET), 4, '0', STR_PAD_LEFT),
        ]);
    }
}
