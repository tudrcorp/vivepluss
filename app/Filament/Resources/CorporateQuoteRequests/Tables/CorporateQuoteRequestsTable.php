<?php

namespace App\Filament\Resources\CorporateQuoteRequests\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Models\Configuration;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;

use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use App\Models\CorporateQuoteRequest;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Actions\DeleteBulkAction;
use App\Http\Controllers\LogController;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use App\Http\Controllers\UtilsController;
use App\Jobs\ResendEmailPropuestaEconomica;
use App\Support\Filament\InternalObservations;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;


class CorporateQuoteRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                    if (Auth::user()->agency_type == 'GENERAL') {
                        $data = CorporateQuoteRequest::query()->where('code_agency', Auth::user()->code_agency);
                    }
                    if (Auth::user()->agency_type == 'MASTER') {
                        $data = CorporateQuoteRequest::query()->where('owner_code', Auth::user()->code_agency);
                    }
                    //Validamos que sea un agente y que pertenezca a la estructura de la agencia Master de la marca Blanca
                    if (Auth::user()->is_agent == 1 || Auth::user()->is_subagent == 1) {
                        $data = CorporateQuoteRequest::query()->where('agent_id', Auth::user()->agent_id);
                    }
                    return $data;
                })
            ->defaultSort('created_at', 'desc')
            ->heading(fn(): string      => Configuration::first()->table_request_table_title == NULL ? 'Solicitudes' : Configuration::first()->table_request_table_title)
            ->description(fn(): string  => Configuration::first()->table_request_table_description == NULL ? '.....' : Configuration::first()->table_request_table_description)
            ->columns([
                TextColumn::make('code')
                    ->label('Codigo')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                TextColumn::make('full_name')
                    ->label('Solicitante')
                    ->searchable(),
                TextColumn::make('rif')
                    ->label('RIF')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Número de teléfono')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable(),
                TextColumn::make('state.definition')
                    ->label('Estado')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('region')
                    ->label('Región')
                    ->searchable(),
                TextColumn::make('poblation')
                    ->label('Poblacion')
                    ->suffix(' Personas')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estatus')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([

                    Action::make('view')
                        ->label('Ver Detalles')
                        ->color('success')
                        ->icon('heroicon-s-eye')
                        ->modalHeading('Detalles de la Cotización')
                        ->modalIcon('heroicon-s-eye')
                        ->modalWidth(Width::ExtraLarge)
                        ->modalSubmitAction(false)
                        ->form([
                            Textarea::make('observations')
                                ->label('Descripción:')
                                ->disabled()
                                ->autoSize()
                                ->default(fn(CorporateQuoteRequest $record) => $record->observations)
                                ->required(),
                        ]),

                    Action::make('add_observations')
                        ->label('Agregar Observaciones')
                        ->icon('heroicon-s-hand-raised')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('OBSERVACIONES DEL AGENTE')
                        ->modalIcon('heroicon-s-hand-raised')
                        ->modalWidth(Width::ExtraLarge)
                        ->modalDescription('Envíanos su inquietud o comentarios!')
                        ->form([
                            Section::make()
                                ->schema([
                                    Textarea::make('description')
                                        ->label('Observaciones')
                                        ->rows(4)
                                ])
                        ])
                        ->action(function (CorporateQuoteRequest $record, array $data) {
                            try {
                                $record->observations = $data['description'];
                                $record->save();

                                Notification::make()
                                    ->body('Las observaciones fueron registradas exitosamente.')
                                    ->success()
                                    ->send();
                            } catch (\Throwable $th) {
                                LogController::log(Auth::user()->id, 'EXCEPTION', 'CorporateQuoteRequestsTable.action.add_observations', $th->getMessage());
                                Notification::make()
                                    ->title('ERROR')
                                    ->body($th->getMessage())
                                    ->icon('heroicon-s-x-circle')
                                    ->iconColor('danger')
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('add_internal_observation')
                        ->label('Observaciones internas')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('info')
                        ->modalHeading('Registrar observación')
                        ->modalDescription('La observación quedará asociada a esta solicitud y al usuario que la registra.')
                        ->modalSubmitActionLabel('Guardar')
                        ->modalWidth(Width::Large)
                        ->form(InternalObservations::formSchema())
                        ->action(function (CorporateQuoteRequest $record, array $data): void {
                            InternalObservations::store($record, 'corporateQuoteRequestObservations', $data);
                        }),
                ])
                    ->icon('heroicon-c-ellipsis-vertical')
                    ->color('azulOscuro')
                    ->hidden(function (CorporateQuoteRequest $record) {
                        return $record->status == 'ANULADA' || $record->status == 'DECLINADA';
                    })
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped();
    }
}