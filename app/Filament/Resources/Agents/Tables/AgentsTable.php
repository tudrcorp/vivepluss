<?php

namespace App\Filament\Resources\Agents\Tables;

use App\Http\Controllers\UtilsController;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\User;
use App\Support\SalesForce\AgencyHierarchy;
use App\Support\SalesForceActivation;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AgentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Una master ve a sus agentes directos y a los de sus agencias
            // generales; una agencia general, solo los suyos. Antes se filtraba
            // por `owner_code = code_agency` a secas, así que la master no veía
            // a nadie que colgara de una general.
            ->query(fn (Builder $query): Builder => Agent::query()
                ->whereIn('owner_code', AgencyHierarchy::visibleAgencyCodes()))
            ->heading('AGENTES')
            ->description('Lista de agentes registrados en el sistema')
            ->columns([
                TextColumn::make('owner_code')
                    ->label('Jerarquía')
                    ->prefix(function ($record) {
                        $agency_type = Agency::select('agency_type_id')
                            ->where('code', $record->owner_code)
                            ->with('typeAgency')
                            ->first();

                        return $agency_type?->typeAgency?->definition
                            ? $agency_type->typeAgency->definition.' - '
                            : 'MASTER - ';
                    })
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-s-building-library')
                    ->searchable(),
                TextColumn::make('id')
                    ->label('Código de agente')
                    ->prefix('AGT-000')
                    ->alignCenter()
                    ->badge()
                    ->color('azulOscuro')
                    ->searchable(),
                TextColumn::make('typeAgent.definition')
                    ->label('Tipo de Agente')
                    ->searchable()
                    ->badge()
                    ->color('verde'),
                TextColumn::make('name')
                    ->label('Razon Social')
                    ->searchable()
                    ->badge()
                    ->color('verde'),
                TextColumn::make('ci')
                    ->label('CI:')
                    ->searchable()
                    ->badge()
                    ->color('verde'),
                TextColumn::make('address')
                    ->label('Direccion')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Número de Teléfono')
                    ->searchable(),
                TextColumn::make('user_instagram')
                    ->label('Usuario de Instagram')
                    ->searchable(),

                IconColumn::make('tdec')
                    ->label('TDEC')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: false),
                IconColumn::make('tdev')
                    ->label('TDEV')
                    ->boolean()
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
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['hasta'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['desde'] ?? null) {
                            $indicators['desde'] = 'Venta desde '.Carbon::parse($data['desde'])->toFormattedDateString();
                        }
                        if ($data['hasta'] ?? null) {
                            $indicators['hasta'] = 'Venta hasta '.Carbon::parse($data['hasta'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
                SelectFilter::make('type_agent')
                    ->label('Tipo agente')
                    ->relationship('typeAgent', 'definition')
                    ->attribute('agent_type_id'),
                SelectFilter::make('status')
                    ->label('Estatus')
                    ->options([
                        'ACTIVO' => 'ACTIVO',
                        'INACTIVO' => 'INACTIVO',
                        'POR REVISION' => 'POR REVISION',
                    ]),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filtros'),
            )
            ->recordActions([
                ActionGroup::make([
                    Action::make('Activate')
                        ->label('Activar Agente')
                        ->action(function (Agent $record) {

                            try {
                                $codeAgent = 'AGT-000'.$record->id;
                                $codeAgency = $record->owner_code;

                                $record->status = 'ACTIVO';
                                $record->code_agency = $codeAgency;
                                $record->code_agent = $codeAgent;
                                $record->save();

                                // 4. creamos el usuario en la tabla users (AGENTES)
                                $user = new User;
                                $user->name = $record->name;
                                $user->email = $record->email;
                                $user->password = Hash::make('12345678');
                                $user->is_agent = true;
                                $user->code_agency = $codeAgency;
                                $user->code_agent = $codeAgent;
                                $user->link_agent = env('APP_URL').'/at/lk/'.Crypt::encryptString($codeAgent);
                                $user->agent_id = $record->id;
                                $user->status = 'ACTIVO';
                                $user->save();

                                SalesForceActivation::notify(
                                    name: $record->name,
                                    email: $record->email,
                                    phone: $record->phone,
                                    roleLabel: 'agente',
                                    whiteCompanyId: $record->white_company_id,
                                );

                                Notification::make()
                                    ->title('AGENTE ACTIVADO')
                                    ->body('El agente ha sido activado exitosamente.')
                                    ->icon('heroicon-s-check-circle')
                                    ->iconColor('success')
                                    ->color('success')
                                    ->send();

                            } catch (\Throwable $th) {
                                Log::error('Falla al activar agente '.$record->id.': '.$th->getMessage());
                                Notification::make()
                                    ->title('EXCEPCION')
                                    ->body('Falla al realizar la activacion. Por favor comuniquese con el administrador.')
                                    ->icon('heroicon-s-x-circle')
                                    ->iconColor('error')
                                    ->color('error')
                                    ->send();
                            }
                        })
                        ->icon('heroicon-s-check-circle')
                        ->color('success')
                        ->requiresConfirmation(),
                    Action::make('Inactivate')
                        ->label('Inactivar Agente')
                        ->requiresConfirmation()
                        ->action(fn (Agent $record) => $record->update(['status' => 'INACTIVO']))
                        ->icon('heroicon-s-x-circle')
                        ->color('danger'),
                    DeleteAction::make()
                        ->color('danger'),
                ])->icon('heroicon-c-ellipsis-vertical')->color('azulOscuro'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('format_phone')
                        ->label('Formatear Teléfonos')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->phone = UtilsController::normalizeVenezuelanPhone($record->phone);
                                $record->save();
                            }
                        })
                        ->requiresConfirmation()
                        ->color('azulOscuro'),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
