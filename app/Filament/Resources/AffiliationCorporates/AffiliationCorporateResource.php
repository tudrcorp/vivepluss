<?php

namespace App\Filament\Resources\AffiliationCorporates;

use App\Filament\Resources\AffiliationCorporates\Pages\CreateAffiliationCorporate;
use App\Filament\Resources\AffiliationCorporates\Pages\EditAffiliationCorporate;
use App\Filament\Resources\AffiliationCorporates\Pages\ListAffiliationCorporates;
use App\Filament\Resources\AffiliationCorporates\Pages\ManageAffiliateCorporates;
use App\Filament\Resources\AffiliationCorporates\RelationManagers\AffiliationCorporatePlansRelationManager;
use App\Filament\Resources\AffiliationCorporates\RelationManagers\CorporateAffiliatesRelationManager;
use App\Filament\Resources\AffiliationCorporates\RelationManagers\ObservationsRelationManager;
use App\Filament\Resources\AffiliationCorporates\RelationManagers\PaidMembershipCorporatesRelationManager;
use App\Filament\Resources\AffiliationCorporates\Schemas\AffiliationCorporateForm;
use App\Filament\Resources\AffiliationCorporates\Tables\AffiliationCorporatesTable;
use App\Models\AffiliationCorporate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class AffiliationCorporateResource extends Resource
{
    protected static ?string $model = AffiliationCorporate::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Consultar Afiliaciones';

    protected static string|UnitEnum|null $navigationGroup = 'CORPORATIVAS';

    protected static ?int $navigationSort = 4;

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
            ObservationsRelationManager::class,
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
            'affiliates' => ManageAffiliateCorporates::route('/{record}/affiliates'),
        ];
    }
}
