<?php

namespace App\Filament\Resources\Zones\Pages;

use App\Filament\Resources\Zones\ZoneResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListZones extends ListRecords
{
    protected static string $resource = ZoneResource::class;

    protected static ?string $title = 'Gestión de Carpetas';

    public function getHeader(): ?View
    {
        return view('filament.resources.zones.list-header');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Nueva Carpeta')
                ->icon('heroicon-m-folder-plus')
                ->extraAttributes(['class' => 'ios-action-btn']),
        ];
    }
}
