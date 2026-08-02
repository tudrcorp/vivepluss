<?php

namespace App\Filament\Resources\DownloadZones;

use App\Filament\Resources\DownloadZones\Pages\CreateDownloadZone;
use App\Filament\Resources\DownloadZones\Pages\EditDownloadZone;
use App\Filament\Resources\DownloadZones\Pages\ListDownloadZones;
use App\Filament\Resources\DownloadZones\Schemas\DownloadZoneForm;
use App\Filament\Resources\DownloadZones\Tables\DownloadZonesTable;
use App\Models\DownloadZone;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class DownloadZoneResource extends Resource
{
    protected static ?string $model = DownloadZone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowDownTray;

    protected static ?string $navigationLabel = 'Documentos';

    protected static string|UnitEnum|null $navigationGroup = 'ZONA DE DESCARGA';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return DownloadZoneForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DownloadZonesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDownloadZones::route('/'),
            'create' => CreateDownloadZone::route('/create'),
            'edit' => EditDownloadZone::route('/{record}/edit'),
        ];
    }

    public static function canManage(): bool
    {
        return Auth::user()?->is_whiteCompanyAdmin == 1
            || Auth::user()?->agency_type == 'MASTER';
    }

    public static function canCreate(): bool
    {
        return static::canManage();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canManage();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canManage();
    }
}
