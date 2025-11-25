<?php

namespace App\Filament\Resources\Agencies\Pages;

use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Agencies\AgencyResource;

class EditAgency extends EditRecord
{
    protected static string $resource = AgencyResource::class;

    public function getTitle(): string
    {
        if (Auth::user()->agency_type == 'GENERAL') {
            return 'Perfil de Agencia';
        }
        return 'Formulario para Registro de Agencias Generales';
    }

}