<?php

namespace App\Filament\Resources\Agents;

use UnitEnum;
use BackedEnum;
use App\Models\Agent;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\Agents\Pages\EditAgent;
use App\Filament\Resources\Agents\Pages\ListAgents;
use App\Filament\Resources\Agents\Pages\CreateAgent;
use App\Filament\Resources\Agents\Schemas\AgentForm;
use App\Filament\Resources\Agents\Tables\AgentsTable;
use App\Support\SalesForce\AgencyHierarchy;

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

    /**
     * Lo ven el administrador de la aliada y la agencia master, y también las
     * agencias generales, que administran a los agentes de su propia
     * estructura. El alcance de cada uno lo decide `AgencyHierarchy`.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return AgencyHierarchy::canManageAgents();
    }

    public static function canAccess(): bool
    {
        return AgencyHierarchy::canManageAgents();
    }

    public static function canCreate(): bool
    {
        return AgencyHierarchy::canManageAgents();
    }
}