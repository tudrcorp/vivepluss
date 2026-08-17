<?php

namespace App\Filament\Resources\IndividualQuotes\Tables;

use App\Http\Controllers\LogController;
use App\Mail\SendMailCertificado;
use App\Mail\SendMailCotizacionIndividual;
use App\Models\Configuration;
use App\Models\IndividualQuote;
use App\Support\Filament\InternalObservations;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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

class IndividualQuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                if (Auth::user()->agency_type == 'MASTER') {
                    $cotizacionesIndividuales = IndividualQuote::query()->where('owner_code', Auth::user()->code_agency);
                }
                if (Auth::user()->agency_type == 'GENERAL') {
                    $cotizacionesIndividuales = IndividualQuote::query()->where('code_agency', Auth::user()->code_agency);
                }
                if (Auth::user()->is_agent == 1 || Auth::user()->is_subagent == 1) {
                    $cotizacionesIndividuales = IndividualQuote::query()->where('agent_id', Auth::user()->agent_id);
                }

                return $cotizacionesIndividuales;
            })
            ->defaultSort('created_at', 'desc')
            ->heading(fn (): string => Configuration::first()->table_quote_ind_table_title == null ? 'Cotizaciones' : Configuration::first()->table_quote_ind_table_title)
            ->description(fn (): string => Configuration::first()->table_quote_ind_table_description == null ? '.....' : Configuration::first()->table_quote_ind_table_description)
            // Card layout on mobile (see resources/css/filament/viveadmin/theme.css,
            // scoped to `.fi-ta-cards-mobile`): trial run for this table only, per
            // product request, before rolling the pattern out to other resources.
            ->stackedOnMobile()
            ->extraAttributes(['class' => 'fi-ta-cards-mobile'])
            ->columns([
                TextColumn::make('code_agency')
                    ->default(fn ($record): string => $record->code_agency ?? '-----')
                    ->label('Agencia')
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-s-building-library')
                    ->searchable(),
                TextColumn::make('agent.name')
                    ->default(fn ($record): string => $record->agent_id ?? '-----')
                    ->label('Agente')
                    ->alignCenter()
                    ->icon('heroicon-m-user')
                    ->weight(FontWeight::SemiBold)
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
                TextColumn::make('full_name')
                    ->label('Solicitada por:')
                    ->icon('heroicon-o-user')
                    ->weight(FontWeight::SemiBold)
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipo de Cotizacion')
                    ->default(function ($record) {
                        if ($record->plan == '1') {
                            return 'Plan Escencial';
                        }
                        if ($record->plan == '2') {
                            return 'Plan Bienestar';
                        }
                        if ($record->plan == '3') {
                            return 'Plan Premium';
                        }
                        if ($record->plan == 'CM') {
                            return 'MultiPlan';
                        }
                        if ($record->plan == null) {
                            return '-----';
                        }
                    })
                    ->badge()
                    ->alignCenter()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'Plan Escencial' => 'primary',
                            'Plan Bienestar' => 'info',
                            'Plan Premium' => 'success',
                            'MultiPlan' => 'warning',
                            default => 'info',
                        };
                    })
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
                            'EJECUTADA' => 'gray',
                            default => 'gray',
                        };
                    })
                    ->icon(function (mixed $state): ?string {
                        return match ($state) {
                            'PRE-APROBADA' => 'heroicon-c-information-circle',
                            'APROBADA' => 'heroicon-s-check-circle',
                            'ANULADA' => 'heroicon-s-exclamation-circle',
                            'DECLINADA' => 'heroicon-c-x-circle',
                            'EJECUTADA' => 'heroicon-s-check-circle',
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

                    /**EMIT */
                    Action::make('emit')
                        ->hidden(function (IndividualQuote $record) {
                            if ($record->status == 'APROBADA') {
                                return true;
                            }

                            return false;
                        })
                        ->label('Aprobar')
                        ->icon('heroicon-m-shield-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('APROBACIÓN DIRECTA PARA PRE-AFILIACIÓN')
                        ->modalIcon('heroicon-m-shield-check')
                        ->modalWidth(Width::ExtraLarge)
                        ->modalDescription(new HtmlString(Blade::render(<<<'BLADE'
                                        <div class="fi-section-header-description mt-5 mb-5">
                                            Felicitaciones!.
                                            <br>
                                        Solo falta completar el formulario de pre-afiliación
                                        </div>
                                BLADE)))
                        ->action(function (IndividualQuote $record) {

                            try {

                                /**
                                 * Actualizo el status a APROBADA
                                 */
                                $record->status = 'APROBADA';
                                $record->save();

                                /**Creamos una variable de session con la cantidad dde personas en la cotizacion */
                                session()->put('persons', $record->detailsQuote()->first()->total_persons);

                                Notification::make()
                                    ->title('COTIZACION INDIVIDUAL APROBADA')
                                    ->body('Nro.'.$record->code.', puede proceder a realizar la pre-afiliación')
                                    ->icon('heroicon-s-user-group')
                                    ->iconColor('success')
                                    ->persistent()
                                    ->success()
                                    ->send();
                                /**
                                 * Redirecciono a la pagina para crear la afiliacion
                                 */
                                $count_plans = $record->detailsQuote()->distinct()->pluck('plan_id');
                                // dd($count_plans[0]);
                                if ($count_plans->count() == 1) {
                                    return redirect()->route('filament.viveadmin.resources.affiliations.create', ['id' => $record->id, 'plan_id' => $count_plans[0]]);
                                }

                                return redirect()->route('filament.viveadmin.resources.affiliations.create', ['id' => $record->id, 'plan_id' => null]);
                            } catch (\Throwable $th) {

                                Notification::make()
                                    ->title('ERROR')
                                    ->body($th->getMessage())
                                    ->icon('heroicon-s-x-circle')
                                    ->iconColor('danger')
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->hidden(function (IndividualQuote $record) {
                            if ($record->status == 'APROBADA' || $record->status == 'EJECUTADA') {
                                return true;
                            }

                            return false;
                        }),

                    /**FORWARD */
                    Action::make('forward')
                        ->label('Reenviar Cotizacion')
                        ->icon('heroicon-o-arrow-uturn-right')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Reenvío de Cotizacion')
                        ->modalWidth(Width::ExtraLarge)
                        ->form([
                            Section::make()
                                ->heading('Informacion')
                                ->description('El link puede sera enviado por email y/o telefono!')
                                ->schema([
                                    TextInput::make('email')
                                        ->label('Email')
                                        ->email(),
                                    // Grid::make(2)->schema([
                                    //     Select::make('country_code')
                                    //         ->label('Código de país')
                                    //         ->options([
                                    //             '+1'   => '🇺🇸 +1 (Estados Unidos)',
                                    //             '+44'  => '🇬🇧 +44 (Reino Unido)',
                                    //             '+49'  => '🇩🇪 +49 (Alemania)',
                                    //             '+33'  => '🇫🇷 +33 (Francia)',
                                    //             '+34'  => '🇪🇸 +34 (España)',
                                    //             '+39'  => '🇮🇹 +39 (Italia)',
                                    //             '+7'   => '🇷🇺 +7 (Rusia)',
                                    //             '+55'  => '🇧🇷 +55 (Brasil)',
                                    //             '+91'  => '🇮🇳 +91 (India)',
                                    //             '+86'  => '🇨🇳 +86 (China)',
                                    //             '+81'  => '🇯🇵 +81 (Japón)',
                                    //             '+82'  => '🇰🇷 +82 (Corea del Sur)',
                                    //             '+52'  => '🇲🇽 +52 (México)',
                                    //             '+58'  => '🇻🇪 +58 (Venezuela)',
                                    //             '+57'  => '🇨🇴 +57 (Colombia)',
                                    //             '+54'  => '🇦🇷 +54 (Argentina)',
                                    //             '+56'  => '🇨🇱 +56 (Chile)',
                                    //             '+51'  => '🇵🇪 +51 (Perú)',
                                    //             '+502' => '🇬🇹 +502 (Guatemala)',
                                    //             '+503' => '🇸🇻 +503 (El Salvador)',
                                    //             '+504' => '🇭🇳 +504 (Honduras)',
                                    //             '+505' => '🇳🇮 +505 (Nicaragua)',
                                    //             '+506' => '🇨🇷 +506 (Costa Rica)',
                                    //             '+507' => '🇵🇦 +507 (Panamá)',
                                    //             '+593' => '🇪🇨 +593 (Ecuador)',
                                    //             '+592' => '🇬🇾 +592 (Guyana)',
                                    //             '+591' => '🇧🇴 +591 (Bolivia)',
                                    //             '+598' => '🇺🇾 +598 (Uruguay)',
                                    //             '+20'  => '🇪🇬 +20 (Egipto)',
                                    //             '+27'  => '🇿🇦 +27 (Sudáfrica)',
                                    //             '+234' => '🇳🇬 +234 (Nigeria)',
                                    //             '+212' => '🇲🇦 +212 (Marruecos)',
                                    //             '+971' => '🇦🇪 +971 (Emiratos Árabes)',
                                    //             '+92'  => '🇵🇰 +92 (Pakistán)',
                                    //             '+880' => '🇧🇩 +880 (Bangladesh)',
                                    //             '+62'  => '🇮🇩 +62 (Indonesia)',
                                    //             '+63'  => '🇵🇭 +63 (Filipinas)',
                                    //             '+66'  => '🇹🇭 +66 (Tailandia)',
                                    //             '+60'  => '🇲🇾 +60 (Malasia)',
                                    //             '+65'  => '🇸🇬 +65 (Singapur)',
                                    //             '+61'  => '🇦🇺 +61 (Australia)',
                                    //             '+64'  => '🇳🇿 +64 (Nueva Zelanda)',
                                    //             '+90'  => '🇹🇷 +90 (Turquía)',
                                    //             '+375' => '🇧🇾 +375 (Bielorrusia)',
                                    //             '+372' => '🇪🇪 +372 (Estonia)',
                                    //             '+371' => '🇱🇻 +371 (Letonia)',
                                    //             '+370' => '🇱🇹 +370 (Lituania)',
                                    //             '+48'  => '🇵🇱 +48 (Polonia)',
                                    //             '+40'  => '🇷🇴 +40 (Rumania)',
                                    //             '+46'  => '🇸🇪 +46 (Suecia)',
                                    //             '+47'  => '🇳🇴 +47 (Noruega)',
                                    //             '+45'  => '🇩🇰 +45 (Dinamarca)',
                                    //             '+41'  => '🇨🇭 +41 (Suiza)',
                                    //             '+43'  => '🇦🇹 +43 (Austria)',
                                    //             '+31'  => '🇳🇱 +31 (Países Bajos)',
                                    //             '+32'  => '🇧🇪 +32 (Bélgica)',
                                    //             '+353' => '🇮🇪 +353 (Irlanda)',
                                    //             '+375' => '🇧🇾 +375 (Bielorrusia)',
                                    //             '+380' => '🇺🇦 +380 (Ucrania)',
                                    //             '+994' => '🇦🇿 +994 (Azerbaiyán)',
                                    //             '+995' => '🇬🇪 +995 (Georgia)',
                                    //             '+976' => '🇲🇳 +976 (Mongolia)',
                                    //             '+998' => '🇺🇿 +998 (Uzbekistán)',
                                    //             '+84'  => '🇻🇳 +84 (Vietnam)',
                                    //             '+856' => '🇱🇦 +856 (Laos)',
                                    //             '+374' => '🇦🇲 +374 (Armenia)',
                                    //             '+965' => '🇰🇼 +965 (Kuwait)',
                                    //             '+966' => '🇸🇦 +966 (Arabia Saudita)',
                                    //             '+972' => '🇮🇱 +972 (Israel)',
                                    //             '+963' => '🇸🇾 +963 (Siria)',
                                    //             '+961' => '🇱🇧 +961 (Líbano)',
                                    //             '+960' => '🇲🇻 +960 (Maldivas)',
                                    //             '+992' => '🇹🇯 +992 (Tayikistán)',
                                    //         ])
                                    //         ->searchable()
                                    //         ->default('+58')
                                    //         ->live(onBlur: true)
                                    //         ->validationMessages([
                                    //             'required'  => 'Campo Requerido',
                                    //         ]),
                                    //     TextInput::make('phone')
                                    //         ->prefixIcon('heroicon-s-phone')
                                    //         ->tel()
                                    //         ->label('Número de teléfono')
                                    //         ->validationMessages([
                                    //             'required'  => 'Campo Requerido',
                                    //         ])
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
                        ->action(function (IndividualQuote $record, array $data) {

                            try {

                                $email = null;
                                $phone = null;

                                if (isset($data['email'])) {
                                    $email = $data['email'];
                                    $cotizacion = $record->code.'.pdf';
                                    Mail::to($email)->send(new SendMailCotizacionIndividual($cotizacion, $record->white_company_id));

                                    Notification::make()
                                        ->title('Certificado enviado')
                                        ->body('Certificado enviado a '.$email)
                                        ->icon('heroicon-o-envelope')
                                        ->iconColor('success')
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

                        }),

                    /* DESCARGAR DOCUMENTO */
                    Action::make('download')
                        ->label('Descargar cotización')
                        ->icon('heroicon-s-arrow-down-on-square-stack')
                        ->color('info')
                        ->action(function (IndividualQuote $record, array $data) {

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
                        }),

                    Action::make('add_internal_observation')
                        ->label('Observaciones internas')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('info')
                        ->modalHeading('Registrar observación')
                        ->modalDescription('La observación quedará asociada a esta cotización y al usuario que la registra.')
                        ->modalSubmitActionLabel('Guardar')
                        ->modalWidth(Width::Large)
                        ->form(InternalObservations::formSchema())
                        ->action(function (IndividualQuote $record, array $data): void {
                            InternalObservations::store($record, 'individualQuoteObservations', $data);
                        }),
                ])->icon('heroicon-c-ellipsis-vertical')->color('gray'),
            ]);
        // ->toolbarActions([
        //     BulkActionGroup::make([
        //         DeleteBulkAction::make(),
        //     ]),
        // ]);
    }
}
