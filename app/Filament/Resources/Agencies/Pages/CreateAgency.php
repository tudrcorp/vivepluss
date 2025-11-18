<?php

namespace App\Filament\Resources\Agencies\Pages;

use App\Filament\Resources\Agencies\AgencyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAgency extends CreateRecord
{
    protected static string $resource = AgencyResource::class;

    protected static ?string $title = 'Formulario para Registro de Agencias Generales';
}