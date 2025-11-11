<?php

namespace App\Filament\Resources\IndividualQuotes;

use App\Filament\Resources\IndividualQuotes\Pages\CreateIndividualQuote;
use App\Filament\Resources\IndividualQuotes\Pages\EditIndividualQuote;
use App\Filament\Resources\IndividualQuotes\Pages\ListIndividualQuotes;
use App\Filament\Resources\IndividualQuotes\Schemas\IndividualQuoteForm;
use App\Filament\Resources\IndividualQuotes\Tables\IndividualQuotesTable;
use App\Models\IndividualQuote;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class IndividualQuoteResource extends Resource
{
    protected static ?string $model = IndividualQuote::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-plus';

    protected static string | UnitEnum | null $navigationGroup = 'INDIVIDUALES';

    protected static ?string $navigationLabel = 'Cotizar';
    
    public static function form(Schema $schema): Schema
    {
        return IndividualQuoteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IndividualQuotesTable::configure($table);
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
            'index' => ListIndividualQuotes::route('/'),
            'create' => CreateIndividualQuote::route('/create'),
            'edit' => EditIndividualQuote::route('/{record}/edit'),
        ];
    }
}