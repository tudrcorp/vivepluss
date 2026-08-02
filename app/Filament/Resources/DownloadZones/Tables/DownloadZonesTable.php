<?php

namespace App\Filament\Resources\DownloadZones\Tables;

use App\Filament\Resources\DownloadZones\DownloadZoneResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DownloadZonesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    ImageColumn::make('image_icon')
                        ->disk('public')
                        ->imageWidth(250)
                        ->imageHeight(250),
                    Stack::make([
                        TextColumn::make('description')
                            ->weight(FontWeight::Bold),
                    ]),
                ])->space(3),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 5,
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->visible(fn (): bool => DownloadZoneResource::canManage()),
                Action::make('download')
                    ->label('Descargar')
                    ->icon('heroicon-o-folder-open')
                    ->color('success')
                    ->url(fn ($record) => asset('storage/'.$record->document))
                    ->button()
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([])
            ->striped();
    }
}
