<?php

namespace App\Filament\Resources\Agents;

use UnitEnum;
use BackedEnum;
use PSpell\Config;
use App\Models\Agent;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\Configuration;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\Agents\Pages\EditAgent;
use App\Filament\Resources\Agents\Pages\ListAgents;
use App\Filament\Resources\Agents\Pages\CreateAgent;
use App\Filament\Resources\Agents\Schemas\AgentForm;
use App\Filament\Resources\Agents\Tables\AgentsTable;

class AgentResource extends Resource
{
    protected static ?string $model = Agent::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Agentes';

    protected static string | UnitEnum | null $navigationGroup = 'ORGANIZACIÓN';

    public static function form(Schema $schema): Schema
    {
        return AgentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgentsTable::configure($table);
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
            'index' => ListAgents::route('/'),
            'create' => CreateAgent::route('/create'),
            'edit' => EditAgent::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        //SI ES UNA AGENCIA MASTER O ES ADMINISTRADOR DE WHITE COMPANY MOSTRAR EL RECURSO EN EL MENÚ
        if (Auth::user()->is_whiteCompanyAdmin == 1 || Auth::user()->agency_type == 'MASTER') {
            $configuration = Configuration::where('white_company_id', Auth::user()->white_company_id)->first()
                ?? Configuration::query()->first();

            if ($configuration?->agents_module_enabled == 1) {
                return true; // ← Muestra el recurso del menú
            }
        }
        return false; // ← No Muestra el recurso del menú
    }
}