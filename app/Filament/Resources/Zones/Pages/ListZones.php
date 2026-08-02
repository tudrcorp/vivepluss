<?php

namespace App\Filament\Resources\Zones\Pages;

use App\Filament\Resources\Zones\ZoneResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListZones extends ListRecords
{
    protected static string $resource = ZoneResource::class;

    protected static ?string $title = 'Gestión de Carpetas';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Nueva Carpeta')
                ->icon('heroicon-m-folder-plus'),
        ];
    }
}
