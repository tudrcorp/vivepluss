<?php

namespace App\Filament\Resources\CorporateQuotes\Pages;

use App\Filament\Resources\CorporateQuotes\CorporateQuoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCorporateQuote extends CreateRecord
{
    protected static string $resource = CorporateQuoteResource::class;
}
