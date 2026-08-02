<?php

namespace App\Filament\Resources\IndividualQuotes\Pages;

use App\Filament\Resources\IndividualQuotes\IndividualQuoteResource;
use Filament\Resources\Pages\EditRecord;

class EditIndividualQuote extends EditRecord
{
    protected static string $resource = IndividualQuoteResource::class;

    protected static ?string $title = 'Editar Cotización Individual';
}
