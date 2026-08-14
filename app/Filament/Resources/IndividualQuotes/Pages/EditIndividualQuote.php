<?php

namespace App\Filament\Resources\IndividualQuotes\Pages;

use App\Filament\Resources\IndividualQuotes\IndividualQuoteResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditIndividualQuote extends EditRecord
{
    protected static string $resource = IndividualQuoteResource::class;

    protected static ?string $title = 'Detalle de la Cotización Individual';

    /**
     * Al hacer clic en una cotización solo debe verse el detalle y las
     * observaciones (los relation managers); el wizard de creación no debe
     * mostrarse ni ser editable desde aquí.
     */
    public function content(Schema $schema): Schema
    {
        return $schema->components([
            $this->getRelationManagersContentComponent(),
        ]);
    }
}
