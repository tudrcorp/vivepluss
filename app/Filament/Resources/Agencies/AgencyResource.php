<?php

namespace App\Filament\Resources\Agencies;

use UnitEnum;
use BackedEnum;
use App\Models\Agency;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\Agencies\Pages\EditAgency;
use App\Filament\Resources\Agencies\Pages\CreateAgency;
use App\Filament\Resources\Agencies\Pages\ListAgencies;
use App\Filament\Resources\Agencies\Schemas\AgencyForm;
use App\Filament\Resources\Agencies\Tables\AgenciesTable;

class AgencyResource extends Resource
{
    protected static ?string $model = Agency::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationLabel = 'Agencias Generales';

    protected static string | UnitEnum | null $navigationGroup = 'ORGANIZACIÓN';

    public static function form(Schema $schema): Schema
    {
        return AgencyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgenciesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAgencies::route('/'),
            'create' => CreateAgency::route('/create'),
            'edit' => EditAgency::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        //SI ES UNA AGENCIA MASTER O ES ADMINISTRADOR DE WHITE COMPANY MOSTRAR EL RECURSO EN EL MENÚ
        if(Auth::user()->is_whiteCompanyAdmin == 1 || Auth::user()->agency_type == 'MASTER') {
            return true; // ← Muestra el recurso del menú
        }
        return false; // ← No Muestra el recurso del menú
    }
}