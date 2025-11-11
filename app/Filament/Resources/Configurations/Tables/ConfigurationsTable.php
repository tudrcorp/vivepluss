<?php

namespace App\Filament\Resources\Configurations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConfigurationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('brandLogo')
                    ->searchable(),
                TextColumn::make('brandLogoHeight')
                    ->searchable(),
                TextColumn::make('favicon')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('agency_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('code_agency')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('brandLogoDefault')
                    ->searchable(),
                TextColumn::make('brandLogoHeightDefault')
                    ->searchable(),
                TextColumn::make('faviconDefault')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
