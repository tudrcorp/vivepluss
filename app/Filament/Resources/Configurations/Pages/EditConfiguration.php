<?php

namespace App\Filament\Resources\Configurations\Pages;

use App\Filament\Resources\Configurations\ConfigurationResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\View\View;

class EditConfiguration extends EditRecord
{
    protected static string $resource = ConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return []; // Opcional: quita el botón "Volver"
    }

    public function getHeader(): ?View
    {
        return view('filament.resources.configurations.edit-header');
    }

    protected function getRedirectUrl(): string
    {
        return Filament::getUrl();
    }
}
