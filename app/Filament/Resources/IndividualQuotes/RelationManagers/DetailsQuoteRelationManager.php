<?php

namespace App\Filament\Resources\IndividualQuotes\RelationManagers;

use App\Filament\Resources\IndividualQuotes\IndividualQuoteResource;
use App\Models\Agency;
use App\Models\Configuration;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DetailsQuoteRelationManager extends RelationManager
{
    protected static string $relationship = 'detailsQuote';

    // protected static ?string $relatedResource = IndividualQuoteResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->heading('DETALLES DE LA COTIZACIÓN')
            ->description('COBERTURAS, TARIFAS AGRUPADAS POR EL RANGO DE EDAD')
            ->recordTitleAttribute('individual_quote_id')
            ->columns([
                TextColumn::make('plan.description')
                    ->label('Plan')
                    ->sortable(),
                TextColumn::make('ageRange.range')
                    ->label('Rango de Edad')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('coverage.price')
                    ->label('Cobertura')
                    ->searchable()
                    ->numeric(decimalPlaces: 0)
                    ->suffix(fn (): string => ' '.Configuration::coverageCurrencySymbol()),
                TextColumn::make('fee')
                    ->label('Tarifa individual')
                    ->alignCenter()
                    ->numeric(decimalPlaces: 0)
                    ->suffix(fn (): string => ' '.Configuration::currencySymbol()),
                TextColumn::make('subtotal_anual')
                    ->label('Total anual')
                    ->alignCenter()
                    ->description(fn ($record): string => $record->total_persons.' personas')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(fn (): string => ' '.Configuration::currencySymbol()),
                TextColumn::make('subtotal_biannual')
                    ->label('Total semestral')
                    ->alignCenter()
                    ->description(fn ($record): string => $record->total_persons.' personas')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(fn (): string => ' '.Configuration::currencySymbol()),
                TextColumn::make('subtotal_quarterly')
                    ->label('Total trimestral')
                    ->alignCenter()
                    ->description(fn ($record): string => $record->total_persons.' personas')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(fn (): string => ' '.Configuration::currencySymbol()),
                TextColumn::make('subtotal_monthly')
                    ->label('Total Mensual')
                    ->alignCenter()
                    ->description(fn ($record): string => $record->total_persons.' personas')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(fn (): string => ' '.Configuration::currencySymbol())
                    ->hidden(fn (): bool => Agency::where('code', Auth::user()->code_agency)->first()->activate_monthly_frequency == 0),
                TextColumn::make('status')
                    ->label('Estatus')
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'PRE-APROBADA' => 'warning',
                            'APROBADA' => 'success',
                            'EJECUTADA' => 'azul',
                        };
                    })
                    ->sortable(),
            ])
            // agrupar por planes y por coberturas
            ->defaultGroup('ageRange.range')
            ->filters([
                SelectFilter::make('coverage_id')
                    ->label('Lista de coberturas')
                    ->relationship('coverage', 'price')
                    ->attribute('sucursal_id'),
            ])
            ->headerActions([
                // CreateAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('prepare_affiliation')
                        ->label('Preparar afiliación')
                        ->color('success')
                        ->icon('heroicon-c-receipt-percent')
                        ->requiresConfirmation()
                        ->modalHeading('Preparar afiliación')
                        ->modalDescription('Se usará la población total de la(s) fila(s) seleccionada(s) para determinar cuántos afiliados debe registrar en el formulario de afiliación.')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records, RelationManager $livewire) {
                            try {
                                if ($records->contains(fn ($record) => $record->status === 'EJECUTADA')) {
                                    Notification::make()
                                        ->title('No es posible afiliar')
                                        ->body('Una o más filas seleccionadas ya fueron ejecutadas en una afiliación anterior.')
                                        ->icon('heroicon-s-x-circle')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $individualQuote = $livewire->getOwnerRecord();

                                /**
                                 * El número de afiliados a registrar es la suma de la población (total_persons)
                                 * de todas las filas seleccionadas: una cotización puede tener varias filas para
                                 * un mismo plan (una por rango de edad), así que sumar es lo correcto en vez de
                                 * tomar solo la primera fila.
                                 */
                                $totalPersons = (int) $records->sum('total_persons');

                                $planIds = $records->pluck('plan_id')->unique();
                                $planId = $planIds->count() === 1 ? $planIds->first() : null;

                                $coverageIds = $records->pluck('coverage_id')->unique();
                                $coverageId = $coverageIds->count() === 1 ? $coverageIds->first() : null;

                                /**
                                 * IDs de las filas de detalle realmente seleccionadas por el analista,
                                 * para que el formulario de afiliación calcule la tarifa solo con esas
                                 * filas en vez de recalcular sobre todas las filas del plan+cobertura
                                 * (que pueden incluir otros rangos de edad de la misma cotización).
                                 */
                                $detailIds = $records->pluck('id')->implode(',');

                                /**
                                 * La información viaja al formulario de afiliación por variable de sesión
                                 * (mismo mecanismo que ya consume AffiliationForm/CreateAffiliation). Se
                                 * elimina en CreateAffiliation::afterCreate() una vez la afiliación queda
                                 * completada, para no dejar datos obsoletos en la sesión del analista.
                                 */
                                session()->put('persons', $totalPersons);

                                $individualQuote->status = 'APROBADA';
                                $individualQuote->save();

                                Notification::make()
                                    ->title('COTIZACIÓN APROBADA')
                                    ->body('Nro. '.$individualQuote->code.', puede proceder a afiliar '.$totalPersons.' persona(s).')
                                    ->icon('heroicon-s-user-group')
                                    ->iconColor('success')
                                    ->persistent()
                                    ->success()
                                    ->send();

                                return redirect()->route('filament.viveadmin.resources.affiliations.create', [
                                    'id' => $individualQuote->id,
                                    'plan_id' => $planId,
                                    'coverage_id' => $coverageId,
                                    'detail_ids' => $detailIds,
                                ]);
                            } catch (\Throwable $th) {
                                Notification::make()
                                    ->title('ERROR')
                                    ->body($th->getMessage())
                                    ->icon('heroicon-s-x-circle')
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->hidden(fn (RelationManager $livewire) => $livewire->getOwnerRecord()->status === 'EJECUTADA'),
                ]),
            ]);
    }
}
