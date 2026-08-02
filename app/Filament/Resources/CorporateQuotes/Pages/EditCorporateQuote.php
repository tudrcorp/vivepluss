<?php

namespace App\Filament\Resources\CorporateQuotes\Pages;

use App\Filament\Resources\CorporateQuotes\CorporateQuoteResource;
use Filament\Resources\Pages\EditRecord;

class EditCorporateQuote extends EditRecord
{
    protected static string $resource = CorporateQuoteResource::class;

    protected static ?string $title = 'Editar Cotización Corporativa';
}
