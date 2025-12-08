<?php

namespace App\Filament\Resources\Agencies\Tables;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Agency;
use App\Models\AgencyType;
use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Models\Configuration;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\Facades\Crypt;
use Filament\Actions\DeleteBulkAction;
use App\Http\Controllers\LogController;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\NotificationController;

class AgenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                $agencies = Agency::query()->where('owner_code', Auth::user()->code_agency);
                return $agencies;
            })
            ->defaultSort('created_at', 'desc')
            ->heading(fn (): string     => Configuration::first()->table_af_corp_table_title == NULL ? 'Agencias' : Configuration::first()->table_af_corp_table_title)
            ->description(fn(): string  => Configuration::first()->table_af_corp_table_description == NULL ? '.....' : Configuration::first()->table_af_corp_table_description)
            ->columns([
                TextColumn::make('owner_code')
                    ->label('De:')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-s-building-library')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('code')
                    ->label('Código')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-building-office-2')
                    ->prefix(function ($record) {
                        $agency_type = AgencyType::select('definition')
                            ->where('id', $record->agency_type_id)
                            ->first()
                            ->definition;

                        return $agency_type . ' - ';
                    })
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('typeAgency.definition')
                    ->label('Tipo agencia')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('name_corporative')
                    ->label('Razon social')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('rif')
                    ->label('RIF:')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('ci_responsable')
                    ->label('Cedula del responsable:')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('address')
                    ->label('Direccion')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('phone')
                    ->label('Número de Teléfono')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('commission_tdec')
                    ->label('(%) TDEC')
                    ->suffix('%')
                    ->badge()
                    ->color(function ($record): string {

                        if ($record->commission_tdec > 0) {
                            return 'success';
                        }
                        return 'warning';
                    })
                    ->default(fn($record): string => $record->commission_tdec ?? '0')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('commission_tdec_renewal')
                    ->label('(%) TDEC Renovacion')
                    ->suffix('%')
                    ->badge()
                    ->color(function ($record): string {

                        if ($record->commission_tdec > 0) {
                            return 'success';
                        }
                        return 'warning';
                    })
                    ->numeric()
                    ->default(fn($record): string => $record->commission_tdec ?? '0')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('commission_tdev')
                    ->label('(%) TDEV')
                    ->suffix('%')
                    ->badge()
                    ->color(function ($record): string {

                        if ($record->commission_tdec > 0) {
                            return 'success';
                        }
                        return 'warning';
                    })
                    ->numeric()
                    ->default(fn($record): string => $record->commission_tdec ?? '0')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('commission_tdev_renewal')
                    ->label('(%) TDEV Renovacion')
                    ->suffix('%')
                    ->badge()
                    ->color(function ($record): string {

                        if ($record->commission_tdec > 0) {
                            return 'success';
                        }
                        return 'warning';
                    })
                    ->numeric()
                    ->default(fn($record): string => $record->commission_tdec ?? '0')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('status')
                    ->label('Estatus')
                    ->badge()
                    ->color(function (mixed $state): string {
                        return match ($state) {
                            'ACTIVO' => 'success',
                            'INACTIVO' => 'danger',
                            'POR REVISION' => 'warning',
                        };
                    })
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('created_by')
                    ->label('Creado Por')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Fecha de Modificación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('desde'),
                        DatePicker::make('hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['hasta'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['desde'] ?? null) {
                            $indicators['desde'] = 'Venta desde ' . Carbon::parse($data['desde'])->toFormattedDateString();
                        }
                        if ($data['hasta'] ?? null) {
                            $indicators['hasta'] = 'Venta hasta ' . Carbon::parse($data['hasta'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('Activate')
                        ->label('ACTIVAR AGENCIA')
                        ->action(function (Agency $record) {

                            try {

                                //1. creamos el usuario en la tabla users para la agencia tipo master o general
                                $user = new User();
                                $user->name = $record->name_corporative;
                                $user->email = $record->email;
                                $user->password = Hash::make('12345678');
                                $user->is_agency = true;
                                $user->code_agency = $record->code;
                                $user->agency_type = $record->agency_type_id == 1 ? 'MASTER' : 'GENERAL';
                                $user->link_agency = env('APP_URL') . '/ay/lk/' . Crypt::encryptString($record->code);
                                $user->status = 'ACTIVO';
                                $user->save();

                                if ($user->save()) {
                                    $record->update(['status' => 'ACTIVO']);
                                }

                                /**
                                 * Notificacion por whatsapp
                                 * @param Agency $record
                                 */
                                $phone = $record->phone;
                                $email = $record->email;
                                $nofitication = NotificationController::agency_activated($record->code, $phone, $email, $record->agency_type_id == 1 ? config('parameters.PATH_MASTER') : config('parameters.PATH_GENERAL'));

                                Notification::make()
                                    ->title('ACTIVACION DE AGENCIA')
                                    ->body('Se ha activado la agencia correctamente.')
                                    ->icon('heroicon-s-check-circle')
                                    ->iconColor('success')
                                    ->color('success')
                                    ->send();
                                    
                            } catch (\Throwable $th) {
                                Log::error($th->getMessage());
                                Notification::make()
                                    ->title('EXCEPCION')
                                    ->body($th->getMessage())
                                    ->icon('heroicon-s-x-circle')
                                    ->iconColor('danger')
                                    ->color('danger')
                                    ->send();
                            }
                        })
                        ->icon('heroicon-s-check-circle')
                        ->color('success')
                        ->requiresConfirmation(),
                    Action::make('Inactivate')
                        ->action(fn(Agency $record) => $record->update(['status' => 'INACTIVO']))
                        ->icon('heroicon-s-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->hidden(fn() => Auth::user()->is_business_admin != 1),
                ])
                    ->icon('heroicon-c-ellipsis-vertical')
                    ->color('info')
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}