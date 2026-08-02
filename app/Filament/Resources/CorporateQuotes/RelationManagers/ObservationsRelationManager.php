<?php

namespace App\Filament\Resources\CorporateQuotes\RelationManagers;

use App\Models\CorporateQuoteObservation;
use App\Support\Filament\InternalObservations;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ObservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'corporateQuoteObservations';

    protected static ?string $title = 'Observaciones internas';

    protected static string|BackedEnum|null $icon = 'heroicon-o-chat-bubble-left-right';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Observaciones internas')
            ->description('Bitácora de notas internas registradas sobre esta cotización corporativa, ordenadas de forma cronológica.')
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('createdBy:id,name,email'))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->description(fn (CorporateQuoteObservation $record): string => $record->created_at?->diffForHumans() ?? '')
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Observación')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('created_by')
                    ->label('Registrado por')
                    ->formatStateUsing(fn (CorporateQuoteObservation $record): string => $record->createdBy?->name ?? (string) ($record->created_by ?? '—'))
                    ->description(fn (CorporateQuoteObservation $record): ?string => $record->createdBy?->email),
            ])
            ->headerActions([
                Action::make('addObservation')
                    ->label('Agregar observación')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('info')
                    ->modalHeading('Registrar observación')
                    ->modalDescription('La observación quedará asociada a esta cotización y al usuario que la registra.')
                    ->modalSubmitActionLabel('Guardar')
                    ->modalWidth(Width::Large)
                    ->form(InternalObservations::formSchema())
                    ->action(function (array $data): void {
                        InternalObservations::store($this->getOwnerRecord(), 'corporateQuoteObservations', $data);
                    }),
            ])
            ->paginated([10, 25, 50]);
    }
}
