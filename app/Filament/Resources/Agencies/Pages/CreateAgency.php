<?php

namespace App\Filament\Resources\Agencies\Pages;

use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Agencies\AgencyResource;

class CreateAgency extends CreateRecord
{
    protected static string $resource = AgencyResource::class;

    public function getTitle(): string
    {
        if(Auth::user()->agency_type == 'GENERAL') {
            return 'Perfil de Agencias Generales';
        }
        return 'Formulario para Registro de Agencias Generales';
    }
}