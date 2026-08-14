<?php

namespace App\Filament\Resources\IndividualQuotes\Pages;

use App\Filament\Resources\IndividualQuotes\IndividualQuoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListIndividualQuotes extends ListRecords
{
    protected static string $resource = IndividualQuoteResource::class;

    protected static ?string $title = 'Cotizaciones Individuales';

    public function getHeader(): ?View
    {
        return view('filament.resources.individual-quotes.list-header');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear cotización individual')
                ->icon('heroicon-s-plus')
                ->extraAttributes(['class' => 'ios-action-btn']),
        ];
    }
}
