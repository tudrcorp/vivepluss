<?php

namespace App\Filament\Resources\Configurations\Pages;

use App\Models\Configuration;
use Filament\Facades\Filament;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Configurations\ConfigurationResource;


class EditConfiguration extends EditRecord
{

    protected static string $resource = ConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return []; // Opcional: quita el botón "Volver"
    }

    protected function getRedirectUrl(): string
    {
        return Filament::getUrl();
    }

}