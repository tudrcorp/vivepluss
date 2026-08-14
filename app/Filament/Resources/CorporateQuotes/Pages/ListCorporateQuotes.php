<?php

namespace App\Filament\Resources\CorporateQuotes\Pages;

use App\Filament\Resources\CorporateQuotes\CorporateQuoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListCorporateQuotes extends ListRecords
{
    protected static string $resource = CorporateQuoteResource::class;

    protected static ?string $title = 'Cotizaciones Corporativas';

    public function getHeader(): ?View
    {
        return view('filament.resources.corporate-quotes.list-header');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear cotización corporativa')
                ->icon('heroicon-s-plus')
                ->extraAttributes(['class' => 'ios-action-btn']),
        ];
    }
}
