<?php

namespace App\Filament\Resources\CorporateQuoteRequests\Pages;

use App\Filament\Resources\CorporateQuoteRequests\CorporateQuoteRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListCorporateQuoteRequests extends ListRecords
{
    protected static string $resource = CorporateQuoteRequestResource::class;

    protected static ?string $title = 'Solicitudes Dress-Taylor';

    public function getHeader(): ?View
    {
        return view('filament.resources.corporate-quote-requests.list-header');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Solicitud')
                ->icon('heroicon-s-plus')
                ->extraAttributes(['class' => 'ios-action-btn']),
        ];
    }
}
