<?php

namespace App\Filament\Resources\CorporateQuotes\Tables;

use App\Filament\Resources\CorporateQuotes\CorporateQuoteResource;
use App\Http\Controllers\LogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UtilsController;
use App\Jobs\SendNotificacionUploadDataCorporate;
use App\Mail\SendMailCotizacionCorporativa;
use App\Models\Agency;
use App\Models\Configuration;
use App\Models\CorporateQuote;
use App\Models\User;
use App\Support\Filament\InternalObservations;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;

class CorporateQuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                if (Auth::user()->agency_type == 'GENERAL') {
                    $cotizacionesCorporativas = CorporateQuote::query()->where('code_agency', Auth::user()->code_agency);
                }
                if (Auth::user()->agency_type == 'MASTER') {
                    $cotizacionesCorporativas = CorporateQuote::query()->where('owner_code', Auth::user()->code_agency);
                }
                // Validamos que sea un agente y que pertenezca a la estructura de la agencia Master de la marca Blanca
                if (Auth::user()->is_agent == 1 || Auth::user()->is_subagent == 1) {
                    $cotizacionesCorporativas = CorporateQuote::query()->where('agent_id', Auth::user()->agent_id);
                }

                return $cotizacionesCorporativas;
            })
            ->defaultSort('created_at', 'desc')
            ->heading(fn (): string => Configuration::first()->table_quote_corp_table_title == null ? 'Cotizaciones' : Configuration::first()->table_quote_corp_table_title)
            ->description(fn (): string => Configuration::first()->table_quote_corp_table_description == null ? '.....' : Configuration::first()->table_quote_corp_table_description)
            ->columns([
                TextColumn::make('code_agency')
                    ->prefix(function ($record) {
                        $agency_type = Agency::select('agency_type_id')
                            ->where('code', $record->code_agency)
                            ->with('typeAgency')
                            ->first();

                        return isset($agency_type) ? $agency_type->typeAgency->definition.' - ' : 'MASTER - ';
                    })
                    ->label('Agencia')
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-s-building-library')
                    ->searchable(),
                TextColumn::make('code')
                    ->label('Código de Cotización')
                    ->badge()
                    ->alignCenter()
                    ->color('primary')
                    ->copyable()
                    ->copyMessage('Código copiado')
                    ->copyMessageDuration(1500)
                    ->searchable(),
                // TextColumn::make('agent.name')
                //     ->label('Agente')
                //     ->badge()
                //     ->default(fn($record): string => $record->agent_id ? $record->agent_id : '-----')
                //     ->color(function (string $state): string {
                //         return match ($state) {
                //             '-----' => 'info',
                //             default => 'success',
                //         };
                //     })
                //     ->icon('heroicon-m-user')
                //     ->searchable(),
                TextColumn::make('full_name')
                    ->label('Solicitada por:')
                    ->icon('heroicon-o-user')
                    ->weight(FontWeight::SemiBold)
                    ->searchable(),
                TextColumn::make('rif')
                    ->label('Rif:')
                    ->icon('heroicon-o-identification')
                    ->copyable()
                    ->copyMessage('Rif copiado')
                    ->copyMessageDuration(1500)
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-m-envelope')
                    ->default(fn ($record): string => $record->email ? $record->email : '-----')
                    ->copyable()
                    ->copyMessage('Email copiado')
                    ->copyMessageDuration(1500)
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Nro. de Teléfono')
                    ->icon('heroicon-m-phone')
                    ->default(fn ($record): string => $record->phone ? $record->phone : '-----')
                    ->copyable()
                    ->copyMessage('Teléfono copiado')
                    ->copyMessageDuration(1500)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Generada el:')
                    ->icon('heroicon-s-calendar')
                    ->description(fn ($record): string => Carbon::parse($record->created_at)->diffForHumans())
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estatus')
                    ->badge()
                    ->alignCenter()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'PRE-APROBADA' => 'info',
                            'APROBADA' => 'success',
                            'ANULADA' => 'warning',
                            'DECLINADA' => 'danger',
                            default => 'gray',
                        };
                    })
                    ->icon(function (mixed $state): ?string {
                        return match ($state) {
                            'PRE-APROBADA' => 'heroicon-c-information-circle',
                            'APROBADA' => 'heroicon-s-check-circle',
                            'ANULADA' => 'heroicon-s-exclamation-circle',
                            'DECLINADA' => 'heroicon-c-x-circle',
                            default => 'heroicon-c-information-circle',
                        };
                    })
                    ->searchable(),
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
                SelectFilter::make('status')
                    ->options([
                        'PRE-APROBADA' => 'PRE-APROBADA',
                        'APROBADA' => 'APROBADA',
                        'EJECUTADA' => 'EJECUTADA',
                    ]),
                SelectFilter::make('plan')
                    ->options([
                        1 => 'Plan Inicial',
                        2 => 'Plan Ideal',
                        3 => 'Plan Especial',
                        'CM' => 'MultiPlan',
                    ])
                    ->label('Tipo de Plan'),
            ])
            ->recordActions([
                ActionGroup::make([

                    Action::make('upload_data_dress_tailor')
                        ->label('Cargar Data')
                        ->icon('heroicon-m-shield-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('DATA DRESS-TAYLOR')
                        ->modalDescription(
                            'Carga de data para la cotización corporativa de Dress Taylor'
                        )
                        ->modalIcon('heroicon-m-shield-check')
                        ->modalWidth(Width::ExtraLarge)
                        ->form([
                            Fieldset::make()
                                ->columnSpanFull()
                                ->schema([
                                    FileUpload::make('data_doc')
                                        ->label('Población')
                                        ->required()
                                        ->visibility('public')
                                        ->helperText('La carga permite archivos .xlsx, .xls, .csv, .txt, .doc, .docx, .pdf, .jpg, .jpeg, .png'),
                                ])->columns(1),
                        ])
                        ->action(function (array $data, $record): void {

                            $record->update([
                                'status' => 'APROBADA-DATA-ENVIADA',
                                'data_doc' => $data['data_doc'],
                            ]);

                            Notification::make()
                                ->title('lLa data fue cargada de forma exitosa.')
                                ->success()
                                ->send();

                            $recipient = User::where('is_admin', 1)->get();
                            foreach ($recipient as $user) {
                                $recipient_for_user = User::find($user->id);
                                Notification::make()
                                    ->title('COTIZACION CORPORATIVA')
                                    ->body('El agente '.Auth::user()->name.' cargo el modelo de data para la cotización Nro. '.$record->code)
                                    ->icon('heroicon-m-tag')
                                    ->iconColor('success')
                                    ->success()
                                    ->actions([
                                        Action::make('view')
                                            ->label('Ver Cotización Corporativa')
                                            ->button()
                                            ->url(CorporateQuoteResource::getUrl('edit', ['record' => $record->id], panel: 'business')),
                                    ])
                                    ->sendToDatabase($recipient_for_user);
                            }

                            // Notificacion por whatsapp
                            NotificationController::sendUploadDataCorporate(Auth::user()->name, $record->code);

                            /**
                             * Notificacion via email
                             * JOB
                             */
                            // SendNotificacionUploadDataCorporate::dispatch($record->data_doc, Auth::user()->name, $record->code);
                        })
                        ->hidden(fn ($record): bool => $record->status == 'APROBADA-DATA-ENVIADA' || $record->status == 'APROBADA' || $record->observation_dress_tailor == null),

                    Action::make('aproved')
                        ->label('Aprobar')
                        ->icon('heroicon-m-shield-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('APROBACIÓN DIRECTA PARA PRE-AFILIACIÓN')
                        ->modalDescription(
                            new HtmlString(
                                Blade::render(
                                    <<<'BLADE'
                                        <div class="fi-section-header-description mt-10">
                                            Por favor cargue la data de la población y a continuación haga click en Confirmar. 
                                            <br>
                                            <br>
                                            💡 Si desea agilizar la gestión puede descargar un archivo de ejemplo haciendo click en los
                                            <strong class="text-gray-900">tres puntos verticales (⋮) de Estatus</strong> 
                                            y seleccionando la opción <strong class="text-gray-900">Formato Data de Población.</strong>
                                            <br>
                                        </div>
                                    BLADE
                                )
                            )
                        )
                        ->modalIcon('heroicon-m-shield-check')
                        ->modalWidth(Width::ExtraLarge)
                        ->form([
                            Fieldset::make()
                                ->columnSpanFull()
                                ->schema([
                                    FileUpload::make('data_doc')
                                        ->label('Población')
                                        ->required()
                                        ->visibility('public')
                                        ->helperText('La carga permite archivos .xlsx, .xls, .csv, .txt, .doc, .docx, .pdf, .jpg, .jpeg, .png'),
                                ])->columns(1),
                        ])
                        ->action(function (array $data, $record): void {

                            $record->update([
                                'status' => 'APROBADA-DATA-ENVIADA',
                                'data_doc' => $data['data_doc'],
                            ]);

                            Notification::make()
                                ->title('lLa data fue cargada de forma exitosa.')
                                ->success()
                                ->send();

                            $recipient = User::where('is_admin', 1)->get();
                            foreach ($recipient as $user) {
                                $recipient_for_user = User::find($user->id);
                                Notification::make()
                                    ->title('COTIZACION CORPORATIVA')
                                    ->body('El agente '.Auth::user()->name.' cargo el modelo de data para la cotización Nro. '.$record->code)
                                    ->icon('heroicon-m-tag')
                                    ->iconColor('success')
                                    ->success()
                                    ->actions([
                                        Action::make('view')
                                            ->label('Ver Cotización Corporativa')
                                            ->button()
                                            ->url(CorporateQuoteResource::getUrl('edit', ['record' => $record->id], panel: 'admin')),
                                    ])
                                    ->sendToDatabase($recipient_for_user);
                            }

                            // Notificacion por whatsapp
                            NotificationController::sendUploadDataCorporate(Auth::user()->name, $record->code);

                            /**
                             * Notificacion via email
                             * JOB
                             */
                            // SendNotificacionUploadDataCorporate::dispatch($record->data_doc, Auth::user()->name, $record->code);
                        })
                        ->hidden(fn ($record): bool => $record->status == 'APROBADA-DATA-ENVIADA' || $record->status == 'APROBADA' || $record->observation_dress_tailor != null),

                    /**REEN\VIO DE COTIZACION CORPORATIVA */
                    Action::make('forward')
                        ->label('Reenviar')
                        ->icon('heroicon-s-link')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-s-link')
                        ->modalHeading('Reenvío de Cotización')
                        ->modalDescription('La propuesta será enviada por correo electrónico y/o teléfono!')
                        ->modalWidth(Width::ExtraLarge)
                        ->form([
                            Section::make()
                                // ->heading('Informacion')
                                // ->description('El link puede sera enviado por email y/o telefono!')
                                ->schema([
                                    TextInput::make('email')
                                        ->label('Correo electrónico')
                                        ->email(),
                                    // Grid::make(2)->schema([
                                    //     Select::make('country_code')
                                    //         ->label('Código de país')
                                    //         ->options(fn() => UtilsController::getCountries())
                                    //         ->searchable()
                                    //         ->default('+58')
                                    //         ->live(onBlur: true),
                                    //     TextInput::make('phone')
                                    //         ->prefixIcon('heroicon-s-phone')
                                    //         ->tel()
                                    //         ->label('Número de teléfono')
                                    //         ->live(onBlur: true)
                                    //         ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                    //             $countryCode = $get('country_code');
                                    //             if ($countryCode) {
                                    //                 $cleanNumber = ltrim(preg_replace('/[^0-9]/', '', $state), '0');
                                    //                 $set('phone', $countryCode . $cleanNumber);
                                    //             }
                                    //         }),
                                    // ])
                                ]),
                        ])
                        ->action(function (CorporateQuote $record, array $data) {

                            try {

                                $email = null;
                                $phone = null;

                                if (isset($data['email'])) {
                                    $email = $data['email'];
                                    $cotizacion = $record->code.'.pdf';
                                    Mail::to($email)->send(new SendMailCotizacionCorporativa($cotizacion));

                                    Notification::make()
                                        ->title('Certificado enviado')
                                        ->body('Certificado enviado a '.$email)
                                        ->icon('heroicon-o-envelope')
                                        ->iconColor('succes')
                                        ->success()
                                        ->send();
                                    // Mail::to('destinatario@example.com')->queue(new SendMailCertificado($certificado));
                                }

                                if (isset($data['phone'])) {
                                    $phone = $data['phone'];
                                }
                            } catch (\Throwable $th) {
                                Log::error($th->getMessage());
                                Notification::make()
                                    ->title('ERROR')
                                    ->body($th->getMessage())
                                    ->icon('heroicon-s-x-circle')
                                    ->iconColor('danger')
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->hidden(fn ($record): bool => $record->observation_dress_tailor != null),

                    /**DESCARGA DE COTIZACION */
                    Action::make('download')
                        ->label('Descargar Cotización')
                        ->icon('heroicon-s-arrow-down-on-square-stack')
                        ->color('info')
                        ->action(function (CorporateQuote $record, array $data) {

                            try {

                                if (! file_exists(public_path('storage/quotes/'.$record->code.'.pdf'))) {

                                    Notification::make()
                                        ->title('NOTIFICACIÓN')
                                        ->body('El documento asociado a la cotización no se encuentra disponible. Por favor, intente nuevamente en unos segundos.')
                                        ->icon('heroicon-s-x-circle')
                                        ->iconColor('warning')
                                        ->warning()
                                        ->send();

                                    return;
                                }
                                /**
                                 * Descargar el documento asociado a la cotizacion
                                 * ruta: storage/
                                 */
                                $path = public_path('storage/quotes/'.$record->code.'.pdf');

                                return response()->download($path);
                            } catch (\Throwable $th) {
                                LogController::log(Auth::user()->id, 'EXCEPTION', 'agents.IndividualQuoteResource.action.enit', $th->getMessage());
                                Notification::make()
                                    ->title('ERROR')
                                    ->body($th->getMessage())
                                    ->icon('heroicon-s-x-circle')
                                    ->iconColor('danger')
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->hidden(fn ($record): bool => $record->observation_dress_tailor != null),

                    /**OBSERVACIONES */
                    Action::make('observations')
                        ->label('Agregar Observaciones')
                        ->icon('heroicon-s-hand-raised')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('OBSERVACIONES DEL AGENTE')
                        ->modalIcon('heroicon-s-hand-raised')
                        ->modalWidth(Width::ExtraLarge)
                        ->modalDescription('Envíanos su inquietud o comentarios!')
                        ->form([
                            Textarea::make('description')
                                ->label('Observaciones')
                                ->rows(5),
                        ])
                        ->action(function (CorporateQuote $record, array $data) {

                            try {

                                $record->observations = $data['description'];
                                $record->save();

                                Notification::make()
                                    ->body('Las observaciones fueron registradas exitosamente.')
                                    ->success()
                                    ->send();

                                $notoficationWp = NotificationController::saddObervationToCorporateQuote($record->code, Auth::user()->name, $data['description']);
                            } catch (\Throwable $th) {
                                LogController::log(Auth::user()->id, 'EXCEPTION', 'agents.IndividualQuoteResource.action.enit', $th->getMessage());
                                Notification::make()
                                    ->title('ERROR')
                                    ->body($th->getMessage())
                                    ->icon('heroicon-s-x-circle')
                                    ->iconColor('danger')
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('download_file')
                        ->label('Formato Data de Población')
                        // ->icon('heroicon-m-download')
                        ->color('info')
                        ->action(function (CorporateQuote $record, array $data) {
                            $path = public_path('storage/files/poblacion_ejemplo.xlsx');

                            return response()->download($path);
                        })
                        ->hidden(fn ($record): bool => $record->observation_dress_tailor != null),

                    Action::make('add_internal_observation')
                        ->label('Observaciones internas')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('info')
                        ->modalHeading('Registrar observación')
                        ->modalDescription('La observación quedará asociada a esta cotización y al usuario que la registra.')
                        ->modalSubmitActionLabel('Guardar')
                        ->modalWidth(Width::Large)
                        ->form(InternalObservations::formSchema())
                        ->action(function (CorporateQuote $record, array $data): void {
                            InternalObservations::store($record, 'corporateQuoteObservations', $data);
                        }),
                ])
                    ->icon('heroicon-c-ellipsis-vertical')
                    ->color('gray')
                    ->hidden(function (CorporateQuote $record) {
                        return $record->status == 'ANULADA' || $record->status == 'DECLINADA';
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
