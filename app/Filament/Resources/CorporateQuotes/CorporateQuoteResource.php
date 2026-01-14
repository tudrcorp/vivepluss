<?php

namespace App\Filament\Resources\CorporateQuotes;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\CorporateQuote;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\CorporateQuotes\Pages\EditCorporateQuote;
use App\Filament\Resources\CorporateQuotes\Pages\ListCorporateQuotes;
use App\Filament\Resources\CorporateQuotes\Pages\CreateCorporateQuote;
use App\Filament\Resources\CorporateQuotes\Schemas\CorporateQuoteForm;
use App\Filament\Resources\CorporateQuotes\Tables\CorporateQuotesTable;
use App\Filament\Resources\CorporateQuotes\RelationManagers\DetailCoporateQuotesRelationManager;

class CorporateQuoteResource extends Resource
{
    protected static ?string $model = CorporateQuote::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-plus';

    protected static string | UnitEnum | null $navigationGroup = 'CORPORATIVAS';

    protected static ?string $navigationLabel = 'Cotizar';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CorporateQuoteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CorporateQuotesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DetailCoporateQuotesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCorporateQuotes::route('/'),
            'create' => CreateCorporateQuote::route('/create'),
            'edit' => EditCorporateQuote::route('/{record}/edit'),
        ];
    }
}