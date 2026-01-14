<?php

namespace App\Filament\Resources\CorporateQuoteRequests;

use App\Filament\Resources\CorporateQuoteRequests\Pages\CreateCorporateQuoteRequest;
use App\Filament\Resources\CorporateQuoteRequests\Pages\EditCorporateQuoteRequest;
use App\Filament\Resources\CorporateQuoteRequests\Pages\ListCorporateQuoteRequests;
use App\Filament\Resources\CorporateQuoteRequests\Schemas\CorporateQuoteRequestForm;
use App\Filament\Resources\CorporateQuoteRequests\Tables\CorporateQuoteRequestsTable;
use App\Models\CorporateQuoteRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CorporateQuoteRequestResource extends Resource
{
    protected static ?string $model = CorporateQuoteRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Solicitud a la Medida';

    protected static string | UnitEnum | null $navigationGroup = 'CORPORATIVAS';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CorporateQuoteRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CorporateQuoteRequestsTable::configure($table);
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
            'index' => ListCorporateQuoteRequests::route('/'),
            'create' => CreateCorporateQuoteRequest::route('/create'),
            'edit' => EditCorporateQuoteRequest::route('/{record}/edit'),
        ];
    }
}