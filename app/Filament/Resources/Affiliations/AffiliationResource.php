<?php

namespace App\Filament\Resources\Affiliations;

use App\Filament\Resources\Affiliations\Pages\CreateAffiliation;
use App\Filament\Resources\Affiliations\Pages\EditAffiliation;
use App\Filament\Resources\Affiliations\Pages\ListAffiliations;
use App\Filament\Resources\Affiliations\Pages\ManageAffiliates;
use App\Filament\Resources\Affiliations\RelationManagers\ObservationsRelationManager;
use App\Filament\Resources\Affiliations\Schemas\AffiliationForm;
use App\Filament\Resources\Affiliations\Tables\AffiliationsTable;
use App\Models\Affiliation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class AffiliationResource extends Resource
{
    protected static ?string $model = Affiliation::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Consultar Afiliaciones';

    protected static string|UnitEnum|null $navigationGroup = 'INDIVIDUALES';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return AffiliationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AffiliationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ObservationsRelationManager::class,
            // AffiliatesRelationManager::class,
            // PaidMembershipsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAffiliations::route('/'),
            'create' => CreateAffiliation::route('/create'),
            'edit' => EditAffiliation::route('/{record}/edit'),
            'affiliates' => ManageAffiliates::route('/{record}/affiliates'),
        ];
    }
}
