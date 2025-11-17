<?php

namespace App\Filament\Resources\Agencies\Pages;

use App\Filament\Resources\Agencies\AgencyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAgencies extends ListRecords
{
    protected static string $resource = AgencyResource::class;

    protected static ?string $title = 'Agencias Generales';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Crear agencia general')
            ->icon('heroicon-s-plus')
        ];
    }
}