<?php

namespace App\Filament\Resources\Agencies\Pages;

use App\Filament\Resources\Agencies\AgencyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListAgencies extends ListRecords
{
    protected static string $resource = AgencyResource::class;

    protected static ?string $title = 'Agencias Generales';

    public function getHeader(): ?View
    {
        return view('filament.resources.agencies.list-header');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear agencia general')
                ->icon('heroicon-s-plus')
                ->extraAttributes(['class' => 'ios-action-btn']),
        ];
    }
}
