<?php

namespace App\Filament\Resources\AffiliationCorporates;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Models\AffiliationCorporate;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\AffiliationCorporates\Pages\EditAffiliationCorporate;
use App\Filament\Resources\AffiliationCorporates\Pages\ListAffiliationCorporates;
use App\Filament\Resources\AffiliationCorporates\Pages\CreateAffiliationCorporate;
use App\Filament\Resources\AffiliationCorporates\Schemas\AffiliationCorporateForm;
use App\Filament\Resources\AffiliationCorporates\Tables\AffiliationCorporatesTable;
use App\Filament\Resources\AffiliationCorporates\RelationManagers\CorporateAffiliatesRelationManager;
use App\Filament\Resources\AffiliationCorporates\RelationManagers\PaidMembershipCorporatesRelationManager;
use App\Filament\Resources\AffiliationCorporates\RelationManagers\AffiliationCorporatePlansRelationManager;
use UnitEnum;

class AffiliationCorporateResource extends Resource
{
    protected static ?string $model = AffiliationCorporate::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Consultar Afiliación';

    protected static string | UnitEnum | null $navigationGroup = 'CORPORATIVAS';
    
    public static function form(Schema $schema): Schema
    {
        return AffiliationCorporateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AffiliationCorporatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // AffiliationCorporatePlansRelationManager::class,
            // CorporateAffiliatesRelationManager::class,
            // PaidMembershipCorporatesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAffiliationCorporates::route('/'),
            'create' => CreateAffiliationCorporate::route('/create'),
            'edit' => EditAffiliationCorporate::route('/{record}/edit'),
        ];
    }
}