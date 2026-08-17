<?php

namespace App\Filament\Resources\Affiliations\Tables;

use App\Filament\Resources\Affiliations\AffiliationResource;
use App\Filament\Resources\Affiliations\Pages\ManageAffiliates;
use App\Http\Controllers\AffiliationController;
use App\Jobs\SendAffiliationDocumentWhatsApp;
use App\Mail\SendMailCertificado;
use App\Mail\WelcomeKitMail;
use App\Models\Affiliation;
use App\Models\AffiliationDocument;
use App\Models\Collection as PaymentCollection;
use App\Models\Configuration;
use App\Models\CreditReconciliation;
use App\Models\User;
use App\Models\WhiteCompany;
use App\Support\AffiliationWelcomeKit;
use App\Support\Filament\InternalObservations;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;

class AffiliationsTable
{
    /**
     * next_payment_date se guarda como varchar "d/m/Y", por lo que un orderBy()
     * normal las ordena alfabéticamente (ej. 2027 antes que 2026). Se ordena
     * convirtiendo el string a fecha real para que las cuotas salgan de menor a mayor.
     */
    /**
     * Certificado y carnet ya no se generan en ViVEplus: el único origen es
     * el webhook de "Regenerar documentos" de Integracorp, que deja el
     * registro más reciente en affiliation_documents (ver AffiliationDocument).
     */
    private static function latestDocument(Affiliation $record, string $documentType): ?AffiliationDocument
    {
        return AffiliationDocument::latestFor($record->code, $documentType);
    }

    private static function pendingCollections(Affiliation $record)
    {
        return PaymentCollection::query()
            ->where('affiliation_code', $record->code)
            ->where('status', 'POR PAGAR')
            ->orderByRaw("STR_TO_DATE(next_payment_date, '%d/%m/%Y') asc")
            ->get();
    }

    /**
     * Resumen visual del crédito de la marca blanca para el fieldset "CRÉDITO" del
     * modal de pago. Muestra tres cifras -asignado (fijo), disponible antes de este
     * pago (ya descontando pagos a crédito previos) y restante después de este
     * pago- para que quede claro que cada pago descuenta del saldo restante real,
     * no del total asignado original.
     */
    private static function renderCreditSummary(int|string|null $whiteCompanyId, string $currency, float $paymentAmount): HtmlString
    {
        $assigned = (float) (WhiteCompany::find($whiteCompanyId)?->assigned_credit ?? 0);
        $availableBefore = CreditReconciliation::remainingCredit($whiteCompanyId);
        $availableAfter = $availableBefore - $paymentAmount;

        $percentAfter = $assigned > 0 ? max(0, min(100, ($availableAfter / $assigned) * 100)) : 0;
        $afterColor = $availableAfter < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400';
        $barColor = $availableAfter < 0 ? 'bg-red-500' : 'bg-emerald-500';

        $assignedFormatted = $currency.' '.number_format($assigned, 2, ',', '.');
        $beforeFormatted = $currency.' '.number_format($availableBefore, 2, ',', '.');
        $afterFormatted = $currency.' '.number_format($availableAfter, 2, ',', '.');

        return new HtmlString(<<<HTML
            <div class="rounded-xl border border-gray-950/10 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Crédito asignado</p>
                        <p class="mt-1 text-lg font-semibold text-gray-600 dark:text-gray-300">{$assignedFormatted}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Disponible antes de este pago</p>
                        <p class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{$beforeFormatted}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Restante luego de este pago</p>
                        <p class="mt-1 text-lg font-bold {$afterColor}">{$afterFormatted}</p>
                    </div>
                </div>
                <div class="mt-4 h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-white/10">
                    <div class="h-full rounded-full {$barColor} transition-all" style="width: {$percentAfter}%;"></div>
                </div>
            </div>
        HTML);
    }

    public static function configure(Table $table): Table
    {
        $currency = Configuration::currencySymbol();
        $currencyName = Configuration::currencyName();
        $currencyNameUpper = mb_strtoupper($currencyName, 'UTF-8');

        return $table
            ->query(function (Builder $query) {
                if (Auth::user()->agency_type == 'GENERAL') {
                    $afiliaciones = Affiliation::query()->where('code_agency', Auth::user()->code_agency);
                }
                if (Auth::user()->agency_type == 'MASTER') {
                    $afiliaciones = Affiliation::query()->where('owner_code', Auth::user()->code_agency);
                }
                // Validamos que sea un agente y que pertenezca a la estructura de la agencia Master de la marca Blanca
                if (Auth::user()->is_agent == 1 || Auth::user()->is_subagent == 1) {
                    $afiliaciones = Affiliation::query()->where('agent_id', Auth::user()->agent_id);
                }

                return $afiliaciones;
            })
            ->defaultSort('created_at', 'desc')
            ->heading(fn (): string => Configuration::first()->table_af_ind_table_title == null ? 'Afiliaciones' : Configuration::first()->table_af_ind_table_title)
            ->description(fn (): string => Configuration::first()->table_af_ind_table_description == null ? '.....' : Configuration::first()->table_af_ind_table_description)
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->icon('heroicon-s-user-group')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('agency.name_corporative')
                    ->label('Agencia')
                    ->badge()
                    ->default(fn ($record): string => $record->code_agency == 'TDG-100' ? 'TUDRENCASA' : '-----')
                    ->color('info')
                    ->searchable(),

                // ...
                // ColumnGroup::make('Plan Afiliado', [
                //     TextColumn::make('plan.description')
                //         ->label('Plan')
                //         ->alignCenter()
                //         ->badge()
                //         ->color('success')
                //         ->searchable(),
                //     TextColumn::make('coverage.price')
                //         ->label('Cobertura')
                //         ->alignCenter()
                //         ->numeric()
                //         ->badge()
                //         ->color('success')
                //         ->suffix(" {$currency}")
                //         ->searchable(),
                //     TextColumn::make('payment_frequency')
                //         ->label('Frecuencia de pago')
                //         ->alignCenter()
                //         ->badge()
                //         ->color('success')
                //         ->searchable(),
                //     TextColumn::make('family_members')
                //         ->label('Población')
                //         ->alignCenter()
                //         ->suffix(' persona(s)')
                //         ->badge()
                //         ->color(function (mixed $state): string {
                //             if ($state > 0) {
                //                 return 'warning';
                //             }
                //             return 'danger';
                //         })
                //         ->searchable(),
                //     TextColumn::make('fee_anual')
                //         ->label('Tarifa Anual')
                //         ->alignCenter()
                //         ->money()
                //         ->badge()
                //         ->color(function (mixed $state): string {
                //             if ($state > 0) {
                //                 return 'warning';
                //             }
                //             return 'danger';
                //         })
                //         ->searchable(),
                // ]),

                // ...
                ColumnGroup::make('Información del Titular', [
                    TextColumn::make('full_name_ti')
                        ->label('Nombre titular')
                        ->icon('heroicon-o-user')
                        ->weight(FontWeight::SemiBold)
                        ->searchable(),
                    TextColumn::make('nro_identificacion_ti')
                        ->label('CI. titular')
                        ->icon('heroicon-o-identification')
                        ->copyable()
                        ->copyMessage('CI copiada')
                        ->copyMessageDuration(1500)
                        ->searchable(),
                    TextColumn::make('phone_ti')
                        ->label('Telefono titular')
                        ->icon('heroicon-m-phone')
                        ->copyable()
                        ->copyMessage('Teléfono copiado')
                        ->copyMessageDuration(1500)
                        ->searchable(),
                    TextColumn::make('email_ti')
                        ->label('Email titular')
                        ->icon('heroicon-m-envelope')
                        ->copyable()
                        ->copyMessage('Email copiado')
                        ->copyMessageDuration(1500)
                        ->searchable(),
                ]),

                // ...
                ColumnGroup::make('Información del Tomador', [
                    TextColumn::make('full_name_payer')
                        ->label('Nombre y Apellido')
                        ->icon('heroicon-o-user')
                        ->weight(FontWeight::SemiBold)
                        ->alignCenter()
                        ->searchable(),
                    TextColumn::make('nro_identificacion_payer')
                        ->label('Numero de Identificación')
                        ->icon('heroicon-o-identification')
                        ->alignCenter()
                        ->copyable()
                        ->copyMessage('Identificación copiada')
                        ->copyMessageDuration(1500)
                        ->searchable(),
                ]),

                TextColumn::make('created_by')
                    ->label('Creado por')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                TextColumn::make('activated_at')
                    ->label('Fecha de Emisión')
                    ->color('warning')
                    ->icon('heroicon-s-calendar')
                    ->badge()
                    ->alignCenter()
                    ->searchable(),

                TextColumn::make('effective_date')
                    ->label('Vigencia')
                    ->color('success')
                    ->icon('heroicon-s-calendar')
                    ->badge()
                    ->alignCenter()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Estatus')
                    ->alignCenter()
                    ->badge()
                    ->color(function (mixed $state): string {
                        return match ($state) {
                            'PRE-APROBADA' => 'success',
                            'ACTIVA' => 'success',
                            'PENDIENTE' => 'warning',
                            'EXCLUIDO' => 'danger',
                        };
                    })
                    ->searchable()
                    ->icon(function (mixed $state): ?string {
                        return match ($state) {
                            'PRE-APROBADA' => 'heroicon-c-information-circle',
                            'ACTIVA' => 'heroicon-s-check-circle',
                            'PENDIENTE' => 'heroicon-s-exclamation-circle',
                            'EXCLUIDO' => 'heroicon-c-x-circle',
                        };
                    }),
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
                SelectFilter::make('plan_id')
                    ->label('Plan(es) afiliado(s)')
                    ->relationship('plan', 'description')
                    ->multiple(),
                SelectFilter::make('payment_frequency')
                    ->label('Frecuencia de Pago')
                    ->options([
                        'ANUAL' => 'ANUAL',
                        'TRIMESTRAL' => 'TRIMESTRAL',
                        'SEMESTRAL' => 'SEMESTRAL',
                    ]),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filtros'),
            )
            ->recordActions([
                ActionGroup::make([

                    Action::make('upload')
                        ->label('Comprobante de Pago')
                        ->color('info')
                        ->icon('heroicon-s-cloud-arrow-up')
                        ->modalWidth(Width::FourExtraLarge)
                        ->form([

                            /** INFORMACION PRINCIPAL */
                            Fieldset::make('INFORMACION PRINCIPAL')
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextInput::make('total_amount')
                                            ->label('Total a pagar')
                                            ->helperText(function ($state, $set, Get $get, Affiliation $record) {
                                                // dd($record->coverage_id);
                                                if (isset($record->coverage_id)) {
                                                    return 'Plan: '.$record->plan->description.' - Cobertura: '.$record->coverage->price.' - Frecuencia: '.$record->payment_frequency;
                                                }

                                                return 'Plan: '.$record->plan->description.' - Frecuencia: '.$record->payment_frequency;
                                            })
                                            ->prefix($currency)
                                            ->default(function ($state, $set, Get $get, Affiliation $record) {
                                                /**
                                                 * Se modifica la logia para buscar el monto a pagar en la tabla
                                                 * de afiliaciones y no en la tabla de cotizaciones
                                                 */
                                                $amount = Affiliation::where('id', $record->id)->first();

                                                return $amount->total_amount;
                                            })
                                            ->numeric()
                                            ->live(),
                                        DatePicker::make('date_payment_voucher')
                                            ->label('Fecha del Comprobante de Pago')
                                            ->required()
                                            ->format('d/m/Y'),
                                    ])->columnSpanFull(),
                                ])->columnSpanFull(),

                            /**FORMA DE PAGO */
                            Fieldset::make('FORMA DE PAGO')
                                ->schema([

                                    /**SELECCION DEL METODO DE PAGO */
                                    Grid::make()
                                        ->schema([
                                            Select::make('payment_method')
                                                ->native(false)
                                                ->label('Método de pago')
                                                ->options([
                                                    'ZELLE' => 'ZELLE',
                                                    'TRANSFERENCIA US$' => "TRANSFERENCIA({$currency})",
                                                    'EFECTIVO US$' => "EFECTIVO {$currency}",
                                                    'MULTIPLE' => 'MULTIPLE',
                                                    'PAGO MOVIL VES' => 'PAGO MOVIL(VES)',
                                                    'TRANSFERENCIA VES' => 'TRANSFERENCIA(VES)',
                                                    'CREDITO' => 'PAGAR A CRÉDITO',

                                                ])
                                                ->live()
                                                ->required()
                                                ->validationMessages([
                                                    'required' => 'Seleccione un tipo de pago',
                                                ]),
                                            TextInput::make('tasa_bcv')
                                                ->live()
                                                ->label('Tasa BCV')
                                                ->helperText('Punto(.) para separar decimales. Ejemplo: 123.45')
                                                ->prefix('VES')
                                                ->numeric()
                                                ->required()
                                                ->validationMessages([
                                                    'required' => 'Campo requerido',
                                                    'numeric' => 'El campo es numerico',
                                                ])
                                                ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                                                    if ($get('payment_method') == 'PAGO MOVIL VES' || $get('payment_method') == 'TRANSFERENCIA VES') {
                                                        $set('pay_amount_ves', $state * $get('total_amount'));
                                                    }

                                                    return $state;
                                                })
                                                ->hidden(function ($state, $set, Get $get) {
                                                    if ($get('payment_method') == 'MULTIPLE' || $get('payment_method') == 'PAGO MOVIL VES' || $get('payment_method') == 'TRANSFERENCIA VES') {
                                                        return false;
                                                    }

                                                    return true;
                                                }),
                                        ])->columnSpan(3),

                                    /* PAGO A CRÉDITO */
                                    Fieldset::make('CRÉDITO')
                                        ->schema([
                                            Placeholder::make('credit_summary_display')
                                                ->hiddenLabel()
                                                ->columnSpanFull()
                                                ->content(function (Affiliation $record, Get $get) use ($currency) {
                                                    return self::renderCreditSummary(
                                                        $record->white_company_id,
                                                        $currency,
                                                        (float) ($get('total_amount') ?? 0),
                                                    );
                                                }),
                                        ])
                                        ->columnSpanFull()
                                        ->hidden(fn (Get $get) => $get('payment_method') !== 'CREDITO'),

                                    /* PAGO EN DOLARES ZELLE */
                                    Fieldset::make("INFORMACION DE PAGO EN ZELLE ({$currency})")
                                        ->schema([
                                            TextInput::make('name_ti_usd')
                                                ->label('Nombre del Titular')
                                                ->helperText('Debe colocar Nombre y Apellido')
                                                ->prefixIcon('heroicon-s-pencil')
                                                ->required()
                                                ->validationMessages([
                                                    'required' => 'Seleccione un tipo de pago',
                                                ]),
                                            TextInput::make('reference_payment_zelle')
                                                ->label('Nro. de Referencia')
                                                ->helperText('Debe colocar el número de referencia completo')
                                                ->prefix('#')
                                                ->regex('/^[A-Za-z0-9\-]+$/')
                                                ->helperText('Solo se permiten letras, números y el guion (-)')
                                                ->required()
                                                ->validationMessages([
                                                    'regex' => 'Solo se permite el guion (-)',
                                                    'required' => 'Seleccione un tipo de pago',
                                                ]),

                                            Grid::make(1)->schema([
                                                FileUpload::make('document_usd')
                                                    ->label("Comprobante({$currency})")
                                                    ->uploadingMessage('Cargando...')
                                                    ->required(),
                                            ]),
                                        ])->columnSpanFull()->hidden(function (Get $get) {
                                            if ($get('payment_method') == 'ZELLE') {
                                                return false;
                                            }

                                            return true;
                                        }),

                                    /** PAGO EN TRANSFERENCIA US$ */
                                    Fieldset::make("INFORMACIÓN DE PAGO EN TRANSFERENCIA ({$currency})")
                                        ->schema([
                                            Grid::make()->schema([
                                                TextInput::make('name_ti_usd')
                                                    ->label('Nombre del Titular')
                                                    ->helperText('Debe colocar Nombre y Apellido')
                                                    ->prefixIcon('heroicon-s-pencil')
                                                    ->required()
                                                    ->validationMessages([
                                                        'required' => 'Campo requerido',
                                                    ]),

                                                Select::make('bank_usd')
                                                    ->native(false)
                                                    ->label('Banco')
                                                    ->live()
                                                    ->required()
                                                    ->validationMessages([
                                                        'required' => 'Seleccione un banco',
                                                    ])
                                                    ->options([
                                                        'CHASE BANK' => 'CHASE BANK',
                                                        'BANK OF AMERICA' => 'BANK OF AMERICA',
                                                        'BANESCO, S.A-US$' => "BANESCO, S.A - {$currency}",
                                                        'BANCAMIGA - US$' => "BANCAMIGA - {$currency}",
                                                        'BANCO DE VENEZUELA - US$' => "BANCO DE VENEZUELA - {$currency}",
                                                    ])
                                                    ->searchable()
                                                    ->live()
                                                    ->prefixIcon('heroicon-s-globe-europe-africa'),

                                                Grid::make(1)->schema([
                                                    FileUpload::make('document_usd')
                                                        ->label("Comprobante({$currency})")
                                                        ->uploadingMessage('Cargando...')
                                                        ->required(),
                                                ]),
                                            ])->columnSpanFull(),
                                        ])->columnSpanFull()->hidden(function (Get $get) {
                                            if ($get('payment_method') == 'TRANSFERENCIA US$') {
                                                return false;
                                            }

                                            return true;
                                        }),

                                    /** PAGO EN EFECTIVO US$ */
                                    Fieldset::make("INFORMACIÓN DE PAGO EN EFECTIVO ({$currency})")
                                        ->schema([
                                            Grid::make(2)->schema([
                                                Select::make('bank_usd')
                                                    ->native(false)
                                                    ->label('Banco')
                                                    ->live()
                                                    ->required()
                                                    ->validationMessages([
                                                        'required' => 'Seleccione un banco',
                                                    ])
                                                    ->options([
                                                        'BANCAMIGA - US$' => "BANCAMIGA - {$currency}",
                                                        'BANCO DE VENEZUELA - US$' => "BANCO DE VENEZUELA - {$currency}",
                                                    ])
                                                    ->searchable()
                                                    ->live()
                                                    ->prefixIcon('heroicon-s-globe-europe-africa'),

                                                Grid::make()->schema([
                                                    FileUpload::make('document_usd')
                                                        ->label("Comprobante({$currency})")
                                                        ->uploadingMessage('Cargando...')
                                                        ->required(),
                                                ])->columnSpanFull(),
                                            ])->hidden(function (Get $get) {
                                                if ($get('payment_method') == 'EFECTIVO US$') {
                                                    return false;
                                                }

                                                return true;
                                            })->columnSpanFull(),

                                        ])->columnSpanFull()->hidden(function (Get $get) {
                                            if ($get('payment_method') == 'EFECTIVO US$') {
                                                return false;
                                            }

                                            return true;
                                        }),

                                    /* PAGO MOVIL Y TRANSFERENCIA */
                                    Fieldset::make('INFORMACIÓN DE PAGO EN MONEDA NACIONAL (VES)')
                                        ->schema([
                                            Grid::make(2)->schema([

                                                TextInput::make('pay_amount_ves')
                                                    ->inputMode('numeric') // activa teclado numérico en móvil
                                                    ->live()
                                                    ->label('Monto a pagar en VES')
                                                    ->helperText('Punto(.) para separar decimales')
                                                    ->prefix('VES')
                                                    ->numeric()
                                                    ->disabled()
                                                    ->dehydrated(),
                                                Select::make('bank_ves')
                                                    ->native(false)
                                                    ->label('Banco')
                                                    ->live()
                                                    ->options([
                                                        'BANCAMIGA(VES)' => 'BANCAMIGA',
                                                        'BANCO DE VENEZUELA(VES)' => 'BANCO DE VENEZUELA',
                                                    ])
                                                    ->searchable()
                                                    ->live()
                                                    ->prefixIcon('heroicon-s-globe-europe-africa')
                                                    ->preload(),
                                                TextInput::make('reference_payment_ves')
                                                    ->label('Referencia de pago(VES)')
                                                    ->live()
                                                    ->inputMode('numeric') // activa teclado numérico en móvil
                                                    ->helperText('Últimos 6 dígitos del comprobante de pago')
                                                    ->mask('999999')
                                                    ->maxLength(6)
                                                    ->rules([
                                                        'regex:/^\d{1,6}$/', // Acepta de 1 a 6 dígitos
                                                    ])
                                                    ->prefix('Ref:'),
                                                Grid::make(1)->schema([
                                                    FileUpload::make('document_ves')
                                                        ->label('Comprobante de pago(VES)')
                                                        ->disk('public')
                                                        ->uploadingMessage('Cargando...')
                                                        ->required(),
                                                ]),

                                            ])->columnSpanFull(),
                                        ])->columnSpanFull()->hidden(function (Get $get) {
                                            if ($get('payment_method') == 'TRANSFERENCIA VES' || $get('payment_method') == 'PAGO MOVIL VES' && $get('tasa_bcv') > 0) {
                                                return false;
                                            }

                                            return true;
                                        }),

                                    /** PAGO MULTIPLE */
                                    Fieldset::make("INFORMACIÓN DE PAGO MULTIPLE EN BOLIVARES (VES) Y {$currencyNameUpper} ({$currency})")
                                        ->schema([
                                            Grid::make(2)->schema([

                                                /* PAGO EN DOLARES(USD)) */
                                                Fieldset::make("PAGO EN {$currencyNameUpper} ({$currency})")
                                                    ->schema([
                                                        /**Metodo de pago en US$ */
                                                        Select::make('payment_method_usd')
                                                            ->live()
                                                            ->native(false)
                                                            ->label("Método de pago en {$currencyName}({$currency})")
                                                            ->options([
                                                                'ZELLE' => 'ZELLE',
                                                                'TRANSFERENCIA US$' => "TRANSFERENCIA({$currency})",
                                                                'EFECTIVO US$' => "EFECTIVO {$currency}",
                                                            ])
                                                            ->required()
                                                            ->validationMessages([
                                                                'required' => 'Seleccione un tipo de pago',
                                                            ]),

                                                        TextInput::make('pay_amount_usd')
                                                            ->inputMode('numeric') // activa teclado numérico en móvil
                                                            ->live(onBlur: true)
                                                            ->label("Monto {$currency}:")
                                                            ->helperText("Punto(.) para separar decimales. Ingresa el monto en {$currencyName}({$currency}).")
                                                            ->prefix($currency)
                                                            ->numeric()
                                                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                                $res = $get('total_amount') - $state;
                                                                Log::info($get('total_amount'));
                                                                Log::info($res);
                                                                Log::info($res / $get('tasa_bcv'));
                                                                $set('pay_amount_ves', $res * $get('tasa_bcv'));
                                                            }),

                                                        TextInput::make('name_ti_usd')
                                                            ->label('Nombre del Titular')
                                                            ->helperText('Debe colocar Nombre y Apellido')
                                                            ->prefixIcon('heroicon-s-pencil')
                                                            ->required()
                                                            ->validationMessages([
                                                                'required' => 'Seleccione un tipo de pago',
                                                            ])
                                                            ->hidden(function (Get $get) {
                                                                if ($get('payment_method_usd') == 'TRANSFERENCIA US$' || $get('payment_method_usd') == 'ZELLE') {
                                                                    return false;
                                                                }

                                                                return true;
                                                            }),

                                                        /**Banco US$ */
                                                        Select::make('bank_usd')
                                                            ->native(false)
                                                            ->label("Banco Moneda Extranjera({$currency})")
                                                            ->live()
                                                            ->options([
                                                                'CHASE BANK' => 'CHASE BANK',
                                                                'BANK OF AMERICA' => 'BANK OF AMERICA',
                                                                'BANESCO, S.A-US$' => "BANESCO, S.A - {$currency}",
                                                                'BANCAMIGA - US$' => "BANCAMIGA - {$currency}",
                                                                'BANCO DE VENEZUELA - US$' => "BANCO DE VENEZUELA - {$currency}",
                                                            ])
                                                            ->searchable()
                                                            ->prefixIcon('heroicon-s-globe-europe-africa'),

                                                        TextInput::make('reference_payment_zelle')
                                                            ->label('Nro. de Referencia')
                                                            ->helperText('Debe colocar el número de referencia completo')
                                                            ->prefix('#')
                                                            ->regex('/^[A-Za-z0-9\-]+$/')
                                                            ->helperText('Solo se permiten letras, números y el guion (-)')
                                                            ->required()
                                                            ->validationMessages([
                                                                'regex' => 'Solo se permite el guion (-)',
                                                                'required' => 'Seleccione un tipo de pago',
                                                            ])
                                                            ->hidden(function (Get $get) {
                                                                if ($get('payment_method_usd') == 'ZELLE') {
                                                                    return false;
                                                                }

                                                                return true;
                                                            }),

                                                        FileUpload::make('document_usd')
                                                            ->label("Comprobante de pago({$currency})")
                                                            ->disk('public')
                                                            ->uploadingMessage('Cargando...'),

                                                    ])->columns(1),

                                                /* PAGO EN BOLIVARES (VES) */
                                                Fieldset::make('PAGO EN BOLIVARES (VES)')
                                                    ->schema([
                                                        /**Metodo de pago en VES */
                                                        Select::make('payment_method_ves')
                                                            ->native(false)
                                                            ->label('Método de pago en bolivares(VES)')
                                                            ->options([
                                                                'PAGO MOVIL VES' => 'PAGO MOVIL(VES)',
                                                                'TRANSFERENCIA VES' => 'TRANSFERENCIA(VES)',
                                                            ])
                                                            ->required()
                                                            ->validationMessages([
                                                                'required' => 'Seleccione un tipo de pago',
                                                            ]),

                                                        TextInput::make('pay_amount_ves')
                                                            ->inputMode('numeric') // activa teclado numérico en móvil
                                                            ->label('Monto VES:')
                                                            ->helperText('Punto(.) para separar decimales. El Sistema calcula el restante en bolivares.')
                                                            ->prefix('VES')
                                                            ->numeric()
                                                            ->disabled()
                                                            ->dehydrated(),

                                                        /**Banco VES */
                                                        Select::make('bank_ves')
                                                            ->native(false)
                                                            ->label('Banco Moneda Nacional(VES)')
                                                            ->options([
                                                                'BANCAMIGA - VES' => 'BANCAMIGA - VES',
                                                                'BANCO DE VENEZUELA - VES' => 'BANCO DE VENEZUELA - VES',
                                                            ])
                                                            ->searchable()
                                                            ->required()
                                                            ->validationMessages([
                                                                'required' => 'Seleccione un banco',
                                                            ])
                                                            ->prefixIcon('heroicon-s-globe-europe-africa'),

                                                        TextInput::make('reference_payment_ves')
                                                            ->label('Referencia de pago(VES)')
                                                            ->inputMode('numeric') // activa teclado numérico en móvil
                                                            ->helperText('Ultimos 6 dígitos del comprobante de pago')
                                                            ->mask('999999')
                                                            ->maxLength(6)
                                                            ->rules([
                                                                'regex:/^\d{1,6}$/', // Acepta de 1 a 6 dígitos
                                                            ])
                                                            ->required()
                                                            ->validationMessages([
                                                                'required' => 'Campo requerido',
                                                            ])
                                                            ->prefix('Ref:'),
                                                        FileUpload::make('document_ves')
                                                            ->label('Comprobante de pago(VES)')
                                                            ->disk('public')
                                                            ->uploadingMessage('Cargando...')
                                                            ->required()
                                                            ->validationMessages([
                                                                'required' => 'El comprobante es requerido',
                                                            ]),
                                                    ])->columns(1),

                                            ])->columnSpanFull(),
                                        ])->columnSpanFull()->hidden(function (Get $get) {
                                            if ($get('payment_method') == 'MULTIPLE' && $get('tasa_bcv') > 0) {
                                                return false;
                                            }

                                            return true;
                                        }),

                                ]),

                            /** CUOTA(S) QUE CUBRE ESTE COMPROBANTE */
                            Fieldset::make('CUOTA(S) A PAGAR')
                                ->schema([
                                    CheckboxList::make('collections')
                                        ->label('Cuotas pendientes de la afiliación')
                                        ->helperText('Selecciona la(s) cuota(s) que se están cancelando con este comprobante. Al guardar quedarán marcadas como pagadas.')
                                        ->options(fn (Affiliation $record) => self::pendingCollections($record)
                                            ->mapWithKeys(fn ($collection) => [
                                                $collection->id => "Cuota N° {$collection->collection_invoice_number}",
                                            ]))
                                        ->descriptions(fn (Affiliation $record) => self::pendingCollections($record)
                                            ->mapWithKeys(fn ($collection) => [
                                                $collection->id => "Vence: {$collection->next_payment_date} · Monto: {$currency} ".number_format((float) $collection->total_amount, 2),
                                            ]))
                                        ->bulkToggleable()
                                        ->columns(1)
                                        ->columnSpanFull(),
                                ])
                                ->columnSpanFull()
                                ->visible(fn (Get $get, Affiliation $record) => $get('payment_method') === 'CREDITO' && self::pendingCollections($record)->isNotEmpty()),

                            /**OBSERVACIONES */
                            Grid::make(1)->schema([
                                Textarea::make('observations_payment')
                                    ->label('Observaciones')
                                    ->rows(2)
                                    ->autosize()
                                    ->dehydrated(),
                            ]),
                        ])
                        ->action(function (Affiliation $record, array $data): void {
                            // dd($data, $record);
                            try {

                                if ($data['payment_method'] === 'CREDITO') {
                                    $remaining = CreditReconciliation::remainingCredit($record->white_company_id);

                                    if ((float) $data['total_amount'] > $remaining) {
                                        Notification::make()
                                            ->title('Crédito insuficiente')
                                            ->body('El total a pagar supera el crédito restante disponible para esta marca blanca.')
                                            ->icon('heroicon-m-x-circle')
                                            ->danger()
                                            ->send();

                                        return;
                                    }
                                }

                                $upload = AffiliationController::uploadPayment($record, $data, 'AGENTE');

                                if ($upload) {
                                    Notification::make()
                                        ->title('NOTIFICACION')
                                        ->body('El comprobante de pago se ha registrado con exito')
                                        ->icon('heroicon-m-user-plus')
                                        ->iconColor('success')
                                        ->success()
                                        ->seconds(5)
                                        ->send();
                                }

                            } catch (\Throwable $th) {
                                Log::error($th->getMessage());
                                Notification::make()
                                    ->title('ERROR')
                                    ->body($th->getMessage())
                                    ->icon('heroicon-m-x-circle')
                                    ->danger()
                                    ->send();
                            }

                        })
                        ->hidden(function (Affiliation $record) {

                            if ($record->payment_frequency == 'ANUAL' && $record->paid_memberships()->count() == 1) {
                                return true;
                            }

                            if ($record->payment_frequency == 'SEMESTRAL' && $record->paid_memberships()->count() == 2) {
                                return true;
                            }

                            if ($record->payment_frequency == 'TRIMESTRAL' && $record->paid_memberships()->count() == 4) {
                                return true;
                            }

                            return false;
                        }),
                    /**DESCARGAR CERTIFICADO */
                    Action::make('download')
                        ->label('Descargar Certificado')
                        ->icon('heroicon-s-arrow-down-on-square-stack')
                        ->color('info')
                        ->action(function (Affiliation $record) {
                            $document = self::latestDocument($record, AffiliationDocument::TYPE_CERTIFICADO);

                            try {
                                if (! $document || ! $document->existsOnDisk()) {
                                    throw new \RuntimeException('El certificado aún no ha sido entregado por Integracorp.');
                                }

                                return response()->download($document->absolutePath());
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
                        ->hidden(fn (Affiliation $record) => ! (self::latestDocument($record, AffiliationDocument::TYPE_CERTIFICADO)?->existsOnDisk() ?? false)),

                    // El carnet es un documento por persona (titular y cada
                    // dependiente tienen el suyo): su descarga vive en
                    // ManageAffiliates.php, por afiliado, no aquí.

                    /**REENVIAR PROPUESTA */
                    Action::make('forward')
                        ->label('Reenviar Certificado')
                        ->icon('heroicon-m-arrows-right-left')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-m-arrows-right-left')
                        ->modalHeading('Reenvío de Certificado')
                        ->modalDescription('El certificado será enviado por email y/o teléfono.')
                        ->modalWidth(Width::ExtraLarge)
                        ->form([
                            Section::make()
                                ->schema([
                                    TextInput::make('email')
                                        ->label('Email')
                                        ->email(),
                                    Grid::make(2)->schema([
                                        Select::make('country_code')
                                            ->label('Código de país')
                                            ->options([
                                                '+1' => '🇺🇸 +1 (Estados Unidos)',
                                                '+44' => '🇬🇧 +44 (Reino Unido)',
                                                '+49' => '🇩🇪 +49 (Alemania)',
                                                '+33' => '🇫🇷 +33 (Francia)',
                                                '+34' => '🇪🇸 +34 (España)',
                                                '+39' => '🇮🇹 +39 (Italia)',
                                                '+7' => '🇷🇺 +7 (Rusia)',
                                                '+55' => '🇧🇷 +55 (Brasil)',
                                                '+91' => '🇮🇳 +91 (India)',
                                                '+86' => '🇨🇳 +86 (China)',
                                                '+81' => '🇯🇵 +81 (Japón)',
                                                '+82' => '🇰🇷 +82 (Corea del Sur)',
                                                '+52' => '🇲🇽 +52 (México)',
                                                '+58' => '🇻🇪 +58 (Venezuela)',
                                                '+57' => '🇨🇴 +57 (Colombia)',
                                                '+54' => '🇦🇷 +54 (Argentina)',
                                                '+56' => '🇨🇱 +56 (Chile)',
                                                '+51' => '🇵🇪 +51 (Perú)',
                                                '+502' => '🇬🇹 +502 (Guatemala)',
                                                '+503' => '🇸🇻 +503 (El Salvador)',
                                                '+504' => '🇭🇳 +504 (Honduras)',
                                                '+505' => '🇳🇮 +505 (Nicaragua)',
                                                '+506' => '🇨🇷 +506 (Costa Rica)',
                                                '+507' => '🇵🇦 +507 (Panamá)',
                                                '+593' => '🇪🇨 +593 (Ecuador)',
                                                '+592' => '🇬🇾 +592 (Guyana)',
                                                '+591' => '🇧🇴 +591 (Bolivia)',
                                                '+598' => '🇺🇾 +598 (Uruguay)',
                                                '+20' => '🇪🇬 +20 (Egipto)',
                                                '+27' => '🇿🇦 +27 (Sudáfrica)',
                                                '+234' => '🇳🇬 +234 (Nigeria)',
                                                '+212' => '🇲🇦 +212 (Marruecos)',
                                                '+971' => '🇦🇪 +971 (Emiratos Árabes)',
                                                '+92' => '🇵🇰 +92 (Pakistán)',
                                                '+880' => '🇧🇩 +880 (Bangladesh)',
                                                '+62' => '🇮🇩 +62 (Indonesia)',
                                                '+63' => '🇵🇭 +63 (Filipinas)',
                                                '+66' => '🇹🇭 +66 (Tailandia)',
                                                '+60' => '🇲🇾 +60 (Malasia)',
                                                '+65' => '🇸🇬 +65 (Singapur)',
                                                '+61' => '🇦🇺 +61 (Australia)',
                                                '+64' => '🇳🇿 +64 (Nueva Zelanda)',
                                                '+90' => '🇹🇷 +90 (Turquía)',
                                                '+375' => '🇧🇾 +375 (Bielorrusia)',
                                                '+372' => '🇪🇪 +372 (Estonia)',
                                                '+371' => '🇱🇻 +371 (Letonia)',
                                                '+370' => '🇱🇹 +370 (Lituania)',
                                                '+48' => '🇵🇱 +48 (Polonia)',
                                                '+40' => '🇷🇴 +40 (Rumania)',
                                                '+46' => '🇸🇪 +46 (Suecia)',
                                                '+47' => '🇳🇴 +47 (Noruega)',
                                                '+45' => '🇩🇰 +45 (Dinamarca)',
                                                '+41' => '🇨🇭 +41 (Suiza)',
                                                '+43' => '🇦🇹 +43 (Austria)',
                                                '+31' => '🇳🇱 +31 (Países Bajos)',
                                                '+32' => '🇧🇪 +32 (Bélgica)',
                                                '+353' => '🇮🇪 +353 (Irlanda)',
                                                '+375' => '🇧🇾 +375 (Bielorrusia)',
                                                '+380' => '🇺🇦 +380 (Ucrania)',
                                                '+994' => '🇦🇿 +994 (Azerbaiyán)',
                                                '+995' => '🇬🇪 +995 (Georgia)',
                                                '+976' => '🇲🇳 +976 (Mongolia)',
                                                '+998' => '🇺🇿 +998 (Uzbekistán)',
                                                '+84' => '🇻🇳 +84 (Vietnam)',
                                                '+856' => '🇱🇦 +856 (Laos)',
                                                '+374' => '🇦🇲 +374 (Armenia)',
                                                '+965' => '🇰🇼 +965 (Kuwait)',
                                                '+966' => '🇸🇦 +966 (Arabia Saudita)',
                                                '+972' => '🇮🇱 +972 (Israel)',
                                                '+963' => '🇸🇾 +963 (Siria)',
                                                '+961' => '🇱🇧 +961 (Líbano)',
                                                '+960' => '🇲🇻 +960 (Maldivas)',
                                                '+992' => '🇹🇯 +992 (Tayikistán)',
                                            ])
                                            ->searchable()
                                            ->default('+58')
                                            ->live(onBlur: true)
                                            ->validationMessages([
                                                'required' => 'Campo Requerido',
                                            ]),
                                        TextInput::make('phone')
                                            ->prefixIcon('heroicon-s-phone')
                                            ->tel()
                                            ->label('Número de teléfono')
                                            ->validationMessages([
                                                'required' => 'Campo Requerido',
                                            ])
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                                $countryCode = $get('country_code');
                                                if ($countryCode) {
                                                    $cleanNumber = ltrim(preg_replace('/[^0-9]/', '', $state), '0');
                                                    $set('phone', $countryCode.$cleanNumber);
                                                }
                                            }),
                                    ]),
                                ]),
                        ])
                        ->action(function (Affiliation $record, array $data) {

                            try {
                                // dd($record, $data);

                                $email = null;
                                $phone = null;

                                if (isset($data['email'])) {
                                    $email = $data['email'];
                                    $document = self::latestDocument($record, AffiliationDocument::TYPE_CERTIFICADO);

                                    if (! $document || ! $document->existsOnDisk()) {
                                        throw new \RuntimeException('El certificado aún no ha sido entregado por Integracorp.');
                                    }

                                    Mail::to($email)->send(new SendMailCertificado($document->absolutePath(), $record->white_company_id));

                                    Notification::make()
                                        ->title('Certificado enviado')
                                        ->body('Certificado enviado a '.$email)
                                        ->icon('heroicon-o-envelope')
                                        ->iconColor('danger')
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
                        ->hidden(fn (Affiliation $record) => ! (self::latestDocument($record, AffiliationDocument::TYPE_CERTIFICADO)?->existsOnDisk() ?? false)),

                    /**
                     * Certificado + carnet(s) + condicionado del plan, agrupados
                     * en un único zip generado al vuelo (AffiliationWelcomeKit).
                     * Solo visible cuando Integracorp ya entregó el certificado y
                     * el carnet de cada afiliado; el condicionado se incluye si
                     * ya fue configurado (ver CondicionadosPorPlan), y si falta
                     * se avisa con una notificación sin bloquear el envío.
                     */
                    ActionGroup::make([
                        Action::make('welcome_kit_download')
                            ->label('Descargar')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->action(function (Affiliation $record) {
                                $kit = AffiliationWelcomeKit::build($record);

                                foreach ($kit['warnings'] as $warning) {
                                    Notification::make()->title('Aviso')->body($warning)->warning()->send();
                                }

                                return response()->download($kit['path'], $kit['filename'])->deleteFileAfterSend();
                            }),

                        Action::make('welcome_kit_email')
                            ->label('Enviar por Email')
                            ->icon('heroicon-o-envelope')
                            ->modalHeading('Enviar Kit de Bienvenida por email')
                            ->modalDescription('El certificado, el/los carnet(s) y el condicionado del plan llegarán como archivos adjuntos separados, para que sea más fácil descargarlos.')
                            ->modalWidth(Width::Large)
                            ->form([
                                TagsInput::make('emails')
                                    ->label('Correos destinatarios')
                                    ->helperText('Presiona Enter después de cada correo.')
                                    ->placeholder('correo@ejemplo.com')
                                    ->default(fn (Affiliation $record) => array_values(array_filter([$record->email_ti])))
                                    ->required(),
                            ])
                            ->action(function (Affiliation $record, array $data) {
                                try {
                                    $kit = AffiliationWelcomeKit::collect($record);

                                    Mail::to($data['emails'])->send(new WelcomeKitMail($record, $kit['files']));

                                    foreach ($kit['warnings'] as $warning) {
                                        Notification::make()->title('Aviso')->body($warning)->warning()->send();
                                    }

                                    Notification::make()
                                        ->title('Kit de Bienvenida enviado')
                                        ->body('Enviado a '.implode(', ', $data['emails']))
                                        ->success()
                                        ->send();
                                } catch (\Throwable $th) {
                                    Log::error($th->getMessage());
                                    Notification::make()
                                        ->title('ERROR')
                                        ->body($th->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            }),

                        Action::make('welcome_kit_whatsapp')
                            ->label('Enviar por WhatsApp')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->modalHeading('Enviar Kit de Bienvenida por WhatsApp')
                            ->modalDescription('El certificado, el/los carnet(s) y el condicionado del plan llegarán como mensajes separados, cada uno con su propio archivo adjunto, para que sea más fácil descargarlos.')
                            ->modalWidth(Width::Large)
                            ->form([
                                TagsInput::make('phones')
                                    ->label('Números de WhatsApp')
                                    ->helperText('Formato internacional sin espacios ni signos, ej. 584242271498. Presiona Enter después de cada número.')
                                    ->placeholder('584242271498')
                                    ->default(fn (Affiliation $record) => array_values(array_filter([preg_replace('/[^0-9]/', '', (string) $record->phone_ti)])))
                                    ->required(),
                            ])
                            ->action(function (Affiliation $record, array $data) {
                                try {
                                    $kit = AffiliationWelcomeKit::collect($record);

                                    $intro = "*Kit de Bienvenida — VivePlus* 🎉\n\n"
                                        ."Hola, tu Kit de Bienvenida para la afiliación *{$record->code}* ya está listo. Te lo enviamos a continuación, un archivo por mensaje.";

                                    foreach ($data['phones'] as $phone) {
                                        SendAffiliationDocumentWhatsApp::dispatch($phone, $intro);

                                        foreach ($kit['files'] as $label => $path) {
                                            SendAffiliationDocumentWhatsApp::dispatch($phone, pathinfo($label, PATHINFO_FILENAME), $path, $label);
                                        }
                                    }

                                    foreach ($kit['warnings'] as $warning) {
                                        Notification::make()->title('Aviso')->body($warning)->warning()->send();
                                    }

                                    Notification::make()
                                        ->title('Kit de Bienvenida enviado')
                                        ->body('Enviado a '.implode(', ', $data['phones']))
                                        ->success()
                                        ->send();
                                } catch (\Throwable $th) {
                                    Log::error($th->getMessage());
                                    Notification::make()
                                        ->title('ERROR')
                                        ->body($th->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            }),
                    ])
                        ->label('Kit de Bienvenida')
                        ->icon('heroicon-o-gift')
                        ->color('success')
                        ->hidden(fn (Affiliation $record) => ! AffiliationWelcomeKit::isReadyFor($record)),

                    Action::make('add_internal_observation')
                        ->label('Observaciones internas')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('info')
                        ->modalHeading('Registrar observación')
                        ->modalDescription('La observación quedará asociada a esta afiliación y al usuario que la registra.')
                        ->modalSubmitActionLabel('Guardar')
                        ->modalWidth(Width::Large)
                        ->form(InternalObservations::formSchema())
                        ->action(function (Affiliation $record, array $data): void {
                            InternalObservations::store($record, 'affiliationObservations', $data);
                        }),

                    Action::make('list_affiliates')
                        ->label('Listar Afiliados')
                        ->icon('heroicon-o-user-group')
                        ->color('gray')
                        ->url(fn (Affiliation $record) => ManageAffiliates::getUrl(['record' => $record], panel: 'viveadmin')),

                ])->hidden(fn ($record) => $record->status == 'EXCLUIDO'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('pay_multiple_affiliations')
                        ->label('Pagar afiliaciones')
                        ->icon('heroicon-m-shield-check')
                        ->color('success')
                        ->modalHeading('PAGO MULTIPLE DE AFILIACIONES')
                        ->modalIcon('heroicon-m-shield-check')
                        ->modalWidth(Width::FourExtraLarge)
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->form(function (Collection $records) {

                            $data = $records->toArray();

                            // guardo la data en la sesion para usarla en el formulario
                            session()->put('data', $data);

                            return [

                                /** INFORMACION PRINCIPAL */
                                Fieldset::make('INFORMACION PRINCIPAL')
                                    ->schema([
                                        Grid::make(1)->schema([
                                            TextInput::make('total_amount')
                                                ->label('Total a pagar')
                                                ->prefix($currency)
                                                ->default(function () {
                                                    return array_sum(array_column(session()->get('data'), 'total_amount'));
                                                })
                                                ->numeric()
                                                ->live(),
                                        ])->columnSpanFull(),
                                    ])->columnSpanFull(),

                                /**FORMA DE PAGO */
                                Fieldset::make('FORMA DE PAGO')
                                    ->schema([

                                        /**SELECCION DEL METODO DE PAGO */
                                        Grid::make()
                                            ->schema([
                                                Select::make('payment_method')
                                                    ->native(false)
                                                    ->label('Método de pago')
                                                    ->options([
                                                        'ZELLE' => 'ZELLE',
                                                        'TRANSFERENCIA US$' => "TRANSFERENCIA({$currency})",
                                                        'EFECTIVO US$' => "EFECTIVO {$currency}",
                                                        'MULTIPLE' => 'MULTIPLE',
                                                        'PAGO MOVIL VES' => 'PAGO MOVIL(VES)',
                                                        'TRANSFERENCIA VES' => 'TRANSFERENCIA(VES)',
                                                        'CREDITO' => 'PAGAR A CRÉDITO',

                                                    ])
                                                    ->live()
                                                    ->required()
                                                    ->validationMessages([
                                                        'required' => 'Seleccione un tipo de pago',
                                                    ]),
                                                TextInput::make('tasa_bcv')
                                                    ->live()
                                                    ->label('Tasa BCV')
                                                    ->helperText('Punto(.) para separar decimales. Ejemplo: 123.45')
                                                    ->prefix('VES')
                                                    ->numeric()
                                                    ->required()
                                                    ->validationMessages([
                                                        'required' => 'Campo requerido',
                                                        'numeric' => 'El campo es numerico',
                                                    ])
                                                    ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                                                        if ($get('payment_method') == 'PAGO MOVIL VES' || $get('payment_method') == 'TRANSFERENCIA VES') {
                                                            $set('pay_amount_ves', $state * $get('total_amount'));
                                                        }

                                                        return $state;
                                                    })
                                                    ->hidden(function ($state, $set, Get $get) {
                                                        if ($get('payment_method') == 'MULTIPLE' || $get('payment_method') == 'PAGO MOVIL VES' || $get('payment_method') == 'TRANSFERENCIA VES') {
                                                            return false;
                                                        }

                                                        return true;
                                                    }),
                                            ])->columnSpan(3),

                                        /* PAGO A CRÉDITO */
                                        Fieldset::make('CRÉDITO')
                                            ->schema([
                                                Placeholder::make('credit_summary_display')
                                                    ->hiddenLabel()
                                                    ->columnSpanFull()
                                                    ->content(function (Get $get) use ($currency) {
                                                        $whiteCompanyId = collect(session()->get('data'))->first()['white_company_id'] ?? null;

                                                        return self::renderCreditSummary(
                                                            $whiteCompanyId,
                                                            $currency,
                                                            (float) ($get('total_amount') ?? 0),
                                                        );
                                                    }),
                                            ])
                                            ->columnSpanFull()
                                            ->hidden(fn (Get $get) => $get('payment_method') !== 'CREDITO'),

                                        /* PAGO EN DOLARES ZELLE */
                                        Fieldset::make("INFORMACION DE PAGO EN ZELLE ({$currency})")
                                            ->schema([
                                                TextInput::make('name_ti_usd')
                                                    ->label('Nombre del Titular')
                                                    ->helperText('Debe colocar Nombre y Apellido')
                                                    ->prefixIcon('heroicon-s-pencil')
                                                    ->required()
                                                    ->validationMessages([
                                                        'required' => 'Seleccione un tipo de pago',
                                                    ]),
                                                TextInput::make('reference_payment_zelle')
                                                    ->label('Nro. de Referencia')
                                                    ->helperText('Debe colocar el número de referencia completo')
                                                    ->prefix('#')
                                                    ->regex('/^[A-Za-z0-9\-]+$/')
                                                    ->helperText('Solo se permiten letras, números y el guion (-)')
                                                    ->required()
                                                    ->validationMessages([
                                                        'regex' => 'Solo se permite el guion (-)',
                                                        'required' => 'Seleccione un tipo de pago',
                                                    ]),

                                                Grid::make(1)->schema([
                                                    FileUpload::make('document_usd')
                                                        ->label("Comprobante({$currency})")
                                                        ->uploadingMessage('Cargando...')
                                                        ->required(),
                                                ]),
                                            ])->columnSpanFull()->hidden(function (Get $get) {
                                                if ($get('payment_method') == 'ZELLE') {
                                                    return false;
                                                }

                                                return true;
                                            }),

                                        /** PAGO EN TRANSFERENCIA US$ */
                                        Fieldset::make("INFORMACIÓN DE PAGO EN TRANSFERENCIA ({$currency})")
                                            ->schema([
                                                Grid::make()->schema([
                                                    TextInput::make('name_ti_usd')
                                                        ->label('Nombre del Titular')
                                                        ->helperText('Debe colocar Nombre y Apellido')
                                                        ->prefixIcon('heroicon-s-pencil')
                                                        ->required()
                                                        ->validationMessages([
                                                            'required' => 'Campo requerido',
                                                        ]),

                                                    Select::make('bank_usd')
                                                        ->native(false)
                                                        ->label('Banco')
                                                        ->live()
                                                        ->required()
                                                        ->validationMessages([
                                                            'required' => 'Seleccione un banco',
                                                        ])
                                                        ->options([
                                                            'CHASE BANK' => 'CHASE BANK',
                                                            'BANK OF AMERICA' => 'BANK OF AMERICA',
                                                            'BANESCO, S.A-US$' => "BANESCO, S.A - {$currency}",
                                                            'BANCAMIGA - US$' => "BANCAMIGA - {$currency}",
                                                            'BANCO DE VENEZUELA - US$' => "BANCO DE VENEZUELA - {$currency}",
                                                        ])
                                                        ->searchable()
                                                        ->live()
                                                        ->prefixIcon('heroicon-s-globe-europe-africa'),

                                                    Grid::make(1)->schema([
                                                        FileUpload::make('document_usd')
                                                            ->label("Comprobante({$currency})")
                                                            ->uploadingMessage('Cargando...')
                                                            ->required(),
                                                    ]),
                                                ])->columnSpanFull(),
                                            ])->columnSpanFull()->hidden(function (Get $get) {
                                                if ($get('payment_method') == 'TRANSFERENCIA US$') {
                                                    return false;
                                                }

                                                return true;
                                            }),

                                        /** PAGO EN EFECTIVO US$ */
                                        Fieldset::make("INFORMACIÓN DE PAGO EN EFECTIVO ({$currency})")
                                            ->schema([
                                                Grid::make(2)->schema([
                                                    Select::make('bank_usd')
                                                        ->native(false)
                                                        ->label('Banco')
                                                        ->live()
                                                        ->required()
                                                        ->validationMessages([
                                                            'required' => 'Seleccione un banco',
                                                        ])
                                                        ->options([
                                                            'BANCAMIGA - US$' => "BANCAMIGA - {$currency}",
                                                            'BANCO DE VENEZUELA - US$' => "BANCO DE VENEZUELA - {$currency}",
                                                        ])
                                                        ->searchable()
                                                        ->live()
                                                        ->prefixIcon('heroicon-s-globe-europe-africa'),

                                                    Grid::make()->schema([
                                                        FileUpload::make('document_usd')
                                                            ->label("Comprobante({$currency})")
                                                            ->uploadingMessage('Cargando...')
                                                            ->required(),
                                                    ])->columnSpanFull(),
                                                ])->hidden(function (Get $get) {
                                                    if ($get('payment_method') == 'EFECTIVO US$') {
                                                        return false;
                                                    }

                                                    return true;
                                                })->columnSpanFull(),

                                            ])->columnSpanFull()->hidden(function (Get $get) {
                                                if ($get('payment_method') == 'EFECTIVO US$') {
                                                    return false;
                                                }

                                                return true;
                                            }),

                                        /* PAGO MOVIL Y TRANSFERENCIA */
                                        Fieldset::make('INFORMACIÓN DE PAGO EN MONEDA NACIONAL (VES)')
                                            ->schema([
                                                Grid::make(2)->schema([

                                                    TextInput::make('pay_amount_ves')
                                                        ->inputMode('numeric') // activa teclado numérico en móvil
                                                        ->live()
                                                        ->label('Monto a pagar en VES')
                                                        ->helperText('Punto(.) para separar decimales')
                                                        ->prefix('VES')
                                                        ->numeric()
                                                        ->disabled()
                                                        ->dehydrated(),
                                                    Select::make('bank_ves')
                                                        ->native(false)
                                                        ->label('Banco')
                                                        ->live()
                                                        ->options([
                                                            'BANCAMIGA(VES)' => 'BANCAMIGA',
                                                            'BANCO DE VENEZUELA(VES)' => 'BANCO DE VENEZUELA',
                                                        ])
                                                        ->searchable()
                                                        ->live()
                                                        ->prefixIcon('heroicon-s-globe-europe-africa')
                                                        ->preload(),
                                                    TextInput::make('reference_payment_ves')
                                                        ->label('Referencia de pago(VES)')
                                                        ->live()
                                                        ->inputMode('numeric') // activa teclado numérico en móvil
                                                        ->helperText('Últimos 6 dígitos del comprobante de pago')
                                                        ->mask('999999')
                                                        ->maxLength(6)
                                                        ->rules([
                                                            'regex:/^\d{1,6}$/', // Acepta de 1 a 6 dígitos
                                                        ])
                                                        ->prefix('Ref:'),
                                                    Grid::make(1)->schema([
                                                        FileUpload::make('document_ves')
                                                            ->label('Comprobante de pago(VES)')
                                                            ->disk('public')
                                                            ->uploadingMessage('Cargando...')
                                                            ->required(),
                                                    ]),

                                                ])->columnSpanFull(),
                                            ])->columnSpanFull()->hidden(function (Get $get) {
                                                if ($get('payment_method') == 'TRANSFERENCIA VES' || $get('payment_method') == 'PAGO MOVIL VES' && $get('tasa_bcv') > 0) {
                                                    return false;
                                                }

                                                return true;
                                            }),

                                        /** PAGO MULTIPLE */
                                        Fieldset::make("INFORMACIÓN DE PAGO MULTIPLE EN BOLIVARES (VES) Y {$currencyNameUpper} ({$currency})")
                                            ->schema([
                                                Grid::make(2)->schema([

                                                    /* PAGO EN DOLARES(USD)) */
                                                    Fieldset::make("PAGO EN {$currencyNameUpper} ({$currency})")
                                                        ->schema([
                                                            /**Metodo de pago en US$ */
                                                            Select::make('payment_method_usd')
                                                                ->live()
                                                                ->native(false)
                                                                ->label("Método de pago en {$currencyName}({$currency})")
                                                                ->options([
                                                                    'ZELLE' => 'ZELLE',
                                                                    'TRANSFERENCIA US$' => "TRANSFERENCIA({$currency})",
                                                                    'EFECTIVO US$' => "EFECTIVO {$currency}",
                                                                ])
                                                                ->required()
                                                                ->validationMessages([
                                                                    'required' => 'Seleccione un tipo de pago',
                                                                ]),

                                                            TextInput::make('pay_amount_usd')
                                                                ->inputMode('numeric') // activa teclado numérico en móvil
                                                                ->live(onBlur: true)
                                                                ->label("Monto {$currency}:")
                                                                ->helperText("Punto(.) para separar decimales. Ingresa el monto en {$currencyName}({$currency}).")
                                                                ->prefix($currency)
                                                                ->numeric()
                                                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                                    $res = $get('total_amount') - $state;
                                                                    Log::info($get('total_amount'));
                                                                    Log::info($res);
                                                                    Log::info($res / $get('tasa_bcv'));
                                                                    $set('pay_amount_ves', $res * $get('tasa_bcv'));
                                                                }),

                                                            TextInput::make('name_ti_usd')
                                                                ->label('Nombre del Titular')
                                                                ->helperText('Debe colocar Nombre y Apellido')
                                                                ->prefixIcon('heroicon-s-pencil')
                                                                ->required()
                                                                ->validationMessages([
                                                                    'required' => 'Seleccione un tipo de pago',
                                                                ])
                                                                ->hidden(function (Get $get) {
                                                                    if ($get('payment_method_usd') == 'TRANSFERENCIA US$' || $get('payment_method_usd') == 'ZELLE') {
                                                                        return false;
                                                                    }

                                                                    return true;
                                                                }),

                                                            /**Banco US$ */
                                                            Select::make('bank_usd')
                                                                ->native(false)
                                                                ->label("Banco Moneda Extranjera({$currency})")
                                                                ->live()
                                                                ->options([
                                                                    'CHASE BANK' => 'CHASE BANK',
                                                                    'BANK OF AMERICA' => 'BANK OF AMERICA',
                                                                    'BANESCO, S.A-US$' => "BANESCO, S.A - {$currency}",
                                                                    'BANCAMIGA - US$' => "BANCAMIGA - {$currency}",
                                                                    'BANCO DE VENEZUELA - US$' => "BANCO DE VENEZUELA - {$currency}",
                                                                ])
                                                                ->searchable()
                                                                ->prefixIcon('heroicon-s-globe-europe-africa'),

                                                            TextInput::make('reference_payment_zelle')
                                                                ->label('Nro. de Referencia')
                                                                ->helperText('Debe colocar el número de referencia completo')
                                                                ->prefix('#')
                                                                ->regex('/^[A-Za-z0-9\-]+$/')
                                                                ->helperText('Solo se permiten letras, números y el guion (-)')
                                                                ->required()
                                                                ->validationMessages([
                                                                    'regex' => 'Solo se permite el guion (-)',
                                                                    'required' => 'Seleccione un tipo de pago',
                                                                ])
                                                                ->hidden(function (Get $get) {
                                                                    if ($get('payment_method_usd') == 'ZELLE') {
                                                                        return false;
                                                                    }

                                                                    return true;
                                                                }),

                                                            FileUpload::make('document_usd')
                                                                ->label("Comprobante de pago({$currency})")
                                                                ->disk('public')
                                                                ->uploadingMessage('Cargando...'),

                                                        ])->columns(1),

                                                    /* PAGO EN BOLIVARES (VES) */
                                                    Fieldset::make('PAGO EN BOLIVARES (VES)')
                                                        ->schema([
                                                            /**Metodo de pago en VES */
                                                            Select::make('payment_method_ves')
                                                                ->native(false)
                                                                ->label('Método de pago en bolivares(VES)')
                                                                ->options([
                                                                    'PAGO MOVIL VES' => 'PAGO MOVIL(VES)',
                                                                    'TRANSFERENCIA VES' => 'TRANSFERENCIA(VES)',
                                                                ])
                                                                ->required()
                                                                ->validationMessages([
                                                                    'required' => 'Seleccione un tipo de pago',
                                                                ]),

                                                            TextInput::make('pay_amount_ves')
                                                                ->inputMode('numeric') // activa teclado numérico en móvil
                                                                ->label('Monto VES:')
                                                                ->helperText('Punto(.) para separar decimales. El Sistema calcula el restante en bolivares.')
                                                                ->prefix('VES')
                                                                ->numeric()
                                                                ->disabled()
                                                                ->dehydrated(),

                                                            /**Banco VES */
                                                            Select::make('bank_ves')
                                                                ->native(false)
                                                                ->label('Banco Moneda Nacional(VES)')
                                                                ->options([
                                                                    'BANCAMIGA - VES' => 'BANCAMIGA - VES',
                                                                    'BANCO DE VENEZUELA - VES' => 'BANCO DE VENEZUELA - VES',
                                                                ])
                                                                ->searchable()
                                                                ->required()
                                                                ->validationMessages([
                                                                    'required' => 'Seleccione un banco',
                                                                ])
                                                                ->prefixIcon('heroicon-s-globe-europe-africa'),

                                                            TextInput::make('reference_payment_ves')
                                                                ->label('Referencia de pago(VES)')
                                                                ->inputMode('numeric') // activa teclado numérico en móvil
                                                                ->helperText('Ultimos 6 dígitos del comprobante de pago')
                                                                ->mask('999999')
                                                                ->maxLength(6)
                                                                ->rules([
                                                                    'regex:/^\d{1,6}$/', // Acepta de 1 a 6 dígitos
                                                                ])
                                                                ->required()
                                                                ->validationMessages([
                                                                    'required' => 'Campo requerido',
                                                                ])
                                                                ->prefix('Ref:'),
                                                            FileUpload::make('document_ves')
                                                                ->label('Comprobante de pago(VES)')
                                                                ->disk('public')
                                                                ->uploadingMessage('Cargando...')
                                                                ->required()
                                                                ->validationMessages([
                                                                    'required' => 'El comprobante es requerido',
                                                                ]),
                                                        ])->columns(1),

                                                ])->columnSpanFull(),
                                            ])->columnSpanFull()->hidden(function (Get $get) {
                                                if ($get('payment_method') == 'MULTIPLE' && $get('tasa_bcv') > 0) {
                                                    return false;
                                                }

                                                return true;
                                            }),

                                    ]),

                                /**OBSERVACIONES */
                                Grid::make(1)->schema([
                                    Textarea::make('observations_payment')
                                        ->label('Observaciones')
                                        ->rows(2)
                                        ->autosize()
                                        ->dehydrated(),
                                ]),
                            ];
                        })
                        ->action(function (Collection $records, array $data) {

                            if ($data['payment_method'] === 'CREDITO') {
                                $neededByWhiteCompany = $records->groupBy('white_company_id')
                                    ->map(fn (Collection $group) => $group->sum('total_amount'));

                                foreach ($neededByWhiteCompany as $whiteCompanyId => $needed) {
                                    if ((float) $needed > CreditReconciliation::remainingCredit($whiteCompanyId)) {
                                        Notification::make()
                                            ->title('Crédito insuficiente')
                                            ->body('El total a pagar de la(s) afiliación(es) seleccionada(s) supera el crédito restante disponible para esa marca blanca.')
                                            ->icon('heroicon-m-x-circle')
                                            ->danger()
                                            ->send();

                                        return;
                                    }
                                }
                            }

                            $upload = AffiliationController::uploadPaymentMultipleAffiliations($records, $data, 'AGENTE');

                            if ($upload) {
                                Notification::make()
                                    ->title('NOTIFICACION')
                                    ->body('El comprobante de pago se ha registrado con exito')
                                    ->icon('heroicon-m-user-plus')
                                    ->iconColor('success')
                                    ->success()
                                    ->seconds(5)
                                    ->send();

                                // Notificacion para Admin
                                foreach ($records as $record) {
                                    $recipient = User::where('is_admin', 1)->get();
                                    foreach ($recipient as $user) {
                                        $recipient_for_user = User::find($user->id);
                                        Notification::make()
                                            ->title('REGISTRO DE COMPROBANTE')
                                            ->body('Se ha registrado un nuevo comprobante de pago de forma exitosa. Afiliacion Nro. '.$record->code)
                                            ->icon('heroicon-m-user-plus')
                                            ->iconColor('success')
                                            ->success()
                                            ->actions([
                                                Action::make('view')
                                                    ->label('Ver detalle de pago')
                                                    ->button()
                                                    ->url(AffiliationResource::getUrl('edit', ['record' => $record->id], panel: 'admin').'?activeRelationManager=1'),
                                            ])
                                            ->sendToDatabase($recipient_for_user);
                                    }
                                }
                            }
                        }),
                ]),
            ]);
    }
}
