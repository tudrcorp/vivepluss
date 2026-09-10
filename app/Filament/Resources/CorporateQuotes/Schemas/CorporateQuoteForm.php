<?php

namespace App\Filament\Resources\CorporateQuotes\Schemas;

use App\Http\Controllers\UtilsController;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\Plan;
use App\Support\Catalog\QuotablePlans;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class CorporateQuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('SOLICITANTE')
                        ->schema([
                            Section::make('data_client')
                                ->heading('¡Bienvenido/a de nuevo! 👋 ')
                                ->description('Estás a punto de comenzar a crear una nueva cotización, por favor ingresa la información del cliente para personalizarla. ¡Puede ver el avance del proceso en la barra de estatus!')
                                ->schema([
                                    Grid::make(4)
                                        ->schema([
                                            Radio::make('type')
                                                ->label('Seleccione el tipo de cotización')
                                                ->live()
                                                ->inline()
                                                ->options([
                                                    'BASICO' => 'BÁSICA',
                                                    // 'DRESS-TAILOR' => 'DRESS-TAYLOR / PLANES A LA MEDIDA',
                                                ])
                                                ->required()
                                                ->default('BASICO'),

                                        ])->columnSpanFull(),
                                    Grid::make(4)
                                        ->schema([
                                            TextInput::make('code')
                                                ->label('Nro. de cotización')
                                                ->prefixIcon('heroicon-m-clipboard-document-check')
                                                ->placeholder('Se generará al guardar')
                                                ->disabled()
                                                ->dehydrated(false)
                                                ->maxLength(255),

                                        ])->columnSpanFull(),
                                    Grid::make(4)
                                        ->schema([
                                            TextInput::make('full_name')
                                                ->label('Nombre de la Empresa')
                                                ->prefixIcon('heroicon-m-user')
                                                ->required()
                                                ->validationMessages([
                                                    'required' => 'Campo requerido',
                                                ])
                                                ->maxLength(255)
                                                ->afterStateUpdatedJs(<<<'JS'
                                                    $set('full_name', $state.toUpperCase());
                                                JS),

                                            Select::make('country_code')
                                                ->label('Código de país')
                                                ->options(UtilsController::getCountries())
                                                ->searchable()
                                                ->default('+58')
                                                ->live(onBlur: true)
                                                ->validationMessages([
                                                    'required' => 'Campo Requerido',
                                                ])
                                                ->hiddenOn('edit'),
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
                                            TextInput::make('email')
                                                ->label('Correo electrónico')
                                                ->prefixIcon('heroicon-m-user')
                                                ->validationMessages([
                                                    'required' => 'Campo requerido',
                                                ])
                                                ->maxLength(255),
                                        ])->columnSpanFull(),
                                    Grid::make(1)
                                        ->schema([
                                            Textarea::make('observation_dress_tailor')
                                                ->label('Especificaciones de la cotización')
                                                ->helperText('Por favor, describa las especificaciones de la cotización de forma detallada del tipo de plan, beneficios, coberturas y rango de edades que debe estar asociados a la solicitud.')
                                                ->required()
                                                ->autosize()
                                                ->hidden(fn (Get $get) => $get('type') == 'BASICO'),

                                        ])->columnSpanFull(),
                                    Hidden::make('status')->default('PRE-APROBADA'),
                                    Hidden::make('created_by')->default(Auth::user()->name),

                                    // Codigo de agencia
                                    Hidden::make('code_agency')->default(function () {
                                        if (Auth::user()->agency_type == 'GENERAL') {
                                            return Auth::user()->code_agency;
                                        }

                                        return Auth::user()->code_agency;
                                    }),

                                    // Calculo de OWNER_CODE segun la agencia que esta conectada o el agente que esta conectado
                                    Hidden::make('owner_code')->default(function () {

                                        // 1.- Agencia GENERAL
                                        if (Auth::user()->agency_type == 'GENERAL') {
                                            $owner = Agency::where('code', Auth::user()->code_agency)->value('owner_code');

                                            return $owner;
                                        }

                                        // 2.- Agencia MASTER
                                        if (Auth::user()->agency_type == 'MASTER') {
                                            return Auth::user()->code_agency;
                                        }

                                        // 3.- Agente o Subagente
                                        if (Auth::user()->is_agent == 1 || Auth::user()->is_subagent == 1) {
                                            $agent = Agent::where('id', Auth::user()->agent_id)->value('owner_code');

                                            return $agent;
                                        }

                                        return null;
                                    }),

                                    // Si manejan agentes
                                    Hidden::make('agent_id')->default(function () {
                                        if (Auth::user()->is_agent == 1) {
                                            return Auth::user()->agent_id;
                                        }

                                        return null;
                                    }),
                                ])
                                ->columns(3)
                                ->columnSpanFull(),
                        ]),
                    Step::make('PLANES A COTIZAR')
                        ->hidden(fn (Get $get) => $get('type') == 'DRESS-TAILOR')
                        ->description('Plan(es) que desea cotizar:')
                        ->schema([
                            Section::make('plans')
                                ->heading('¡Sección de planes a cotizar! 🎯')
                                // ->description('¡Perfecto! selecciona la que mejor se ajuste a las necesidades del cliente y continúa con el proceso.')
                                ->schema([
                                    Radio::make('plan')
                                        ->columns(3)
                                        ->label('Selecciona el/los planes que desea cotizar:')
                                        ->required()
                                        ->live()
                                        // Incluye los planes que Integracorp asignó a la
                                        // aliada, con su rango de edad real.
                                        ->options(fn (): array => QuotablePlans::options())
                                        ->descriptions(fn (): array => QuotablePlans::descriptions()),
                                ]),
                        ]),
                    Step::make('RANGO DE EDAD')
                        ->hidden(fn (Get $get) => $get('type') == 'DRESS-TAILOR')
                        ->description('Rango de edad y/o población:')
                        ->schema([
                            Section::make('age_range')
                                ->heading('¡Listo para el último paso! 🏁')
                                // ->description(new HtmlString(Blade::render(<<<BLADE
                                //         <div class="fi-section-header-description">
                                //             Por favor, selecciona el rango de edades de los beneficiarios. Al hacerlo, habrás finalizado la configuración principal de la cotización y estarás a un clic de generar el resultado final.
                                //             <br>
                                //             ¡Gracias por tu gran trabajo!
                                //         </div>
                                //     BLADE))
                                // )
                                ->schema([

                                    /**
                                     * REPETER PLAN INICIAl
                                     */
                                    Repeater::make('details_quote_plan_inicial')
                                        ->grid(4)
                                        ->label('Indique edad y número de afiliados al plan:')
                                        ->defaultItems(fn (): int => QuotablePlans::ageRangeCount(1))
                                        ->addable(false)
                                        ->hidden(function (Get $get) {
                                            if ($get('plan') == 1) {
                                                return false;
                                            }

                                            return true;
                                        })
                                        ->table([
                                            TableColumn::make('Rango de Edad')->width('150px'),
                                            TableColumn::make('Total de personas'),
                                        ])
                                        ->schema([
                                            Hidden::make('plan_id')->default(1),
                                            Radio::make('age_range_id')
                                                ->label(false)

                                                ->inLine()

                                                ->disableOptionWhen(function ($value, $state, Get $get) {
                                                    return collect($get('../*.age_range_id'))
                                                        ->reject(fn ($id) => $id == $state)
                                                        ->filter()
                                                        ->contains($value);
                                                })
                                                ->options(fn (): array => QuotablePlans::ageRangeOptions(1))->columnSpan(4),
                                            TextInput::make('total_persons')
                                                ->placeholder('Cantidad de personas'),
                                        ])->columns(2),

                                    /**
                                     * REPETER PLAN IDEAL
                                     */
                                    Repeater::make('details_quote_plan_ideal')
                                        ->grid(2)
                                        ->label('Indique edad y número de afiliados al plan:')
                                        ->defaultItems(fn (): int => QuotablePlans::ageRangeCount(2))
                                        ->addable(false)
                                        ->hidden(function (Get $get) {
                                            if ($get('plan') == 2) {
                                                return false;
                                            }

                                            return true;
                                        })
                                        ->table([
                                            TableColumn::make('Rango de Edad')->width('300px'),
                                            TableColumn::make('Total de personas'),
                                        ])
                                        ->schema([
                                            Hidden::make('plan_id')->default(2),
                                            Radio::make('age_range_id')
                                                ->label(false)
                                                ->inLine()
                                                ->live()
                                                ->disableOptionWhen(function ($value, $state, Get $get) {
                                                    return collect($get('../*.age_range_id'))
                                                        ->reject(fn ($id) => $id == $state)
                                                        ->filter()
                                                        ->contains($value);
                                                })
                                                ->options(fn (): array => QuotablePlans::ageRangeOptions(2))->columnSpan(4),
                                            TextInput::make('total_persons')
                                                ->placeholder('Cantidad de personas'),
                                        ])->columns(4),

                                    /**
                                     * REPETER PLAN ESPECIAL
                                     */
                                    Repeater::make('details_quote_plan_especial')
                                        ->grid(2)
                                        ->label('Indique edad y número de afiliados al plan:')
                                        ->defaultItems(fn (): int => QuotablePlans::ageRangeCount(3))
                                        ->addable(false)
                                        ->hidden(function (Get $get) {
                                            if ($get('plan') == 3) {
                                                return false;
                                            }

                                            return true;
                                        })
                                        ->table([
                                            TableColumn::make('Rango de Edad')->width('380px'),
                                            TableColumn::make('Total de personas'),
                                        ])
                                        ->schema([
                                            Hidden::make('plan_id')->default(3),
                                            Radio::make('age_range_id')
                                                ->label(false)
                                                ->inLine()
                                                ->live()
                                                ->disableOptionWhen(function ($value, $state, Get $get) {
                                                    return collect($get('../*.age_range_id'))
                                                        ->reject(fn ($id) => $id == $state)
                                                        ->filter()
                                                        ->contains($value);
                                                })
                                                ->options(fn (): array => QuotablePlans::ageRangeOptions(3))->columnSpan(4),
                                            TextInput::make('total_persons')
                                                ->placeholder('Cantidad de personas'),
                                        ])->columns(2),

                                    /**
                                     * REPETER PLAN MULTIPLE
                                     */
                                    /**
                                     * REPETER PLAN ASIGNADO
                                     *
                                     * Los tres planes históricos tienen su propio repeater arriba;
                                     * cualquier plan que Integracorp asigne a la aliada usa este,
                                     * que se arma con los rangos de edad del plan elegido.
                                     */
                                    Repeater::make('details_quote_plan_asignado')
                                        ->grid(2)
                                        ->label('Indique edad y número de afiliados al plan:')
                                        ->defaultItems(fn (Get $get) => max(1, QuotablePlans::ageRangeCount($get('plan'))))
                                        ->addable(false)
                                        ->hidden(fn (Get $get): bool => ! QuotablePlans::usesGenericRepeater($get('plan')))
                                        ->table([
                                            TableColumn::make('Rango de Edad')->width('300px'),
                                            TableColumn::make('Total de personas'),
                                        ])
                                        ->schema([
                                            Radio::make('age_range_id')
                                                ->label(false)
                                                ->inLine()
                                                ->live()
                                                ->disableOptionWhen(function ($value, $state, Get $get) {
                                                    return collect($get('../*.age_range_id'))
                                                        ->reject(fn ($id) => $id == $state)
                                                        ->filter()
                                                        ->contains($value);
                                                })
                                                ->options(fn (Get $get) => QuotablePlans::ageRangeOptions($get('../../plan')))
                                                ->columnSpan(4),
                                            TextInput::make('total_persons')
                                                ->placeholder('Cantidad de personas'),
                                        ])->columns(4),

                                    Repeater::make('details_quote')
                                        ->label('Indique los planes, la edad y número de personas:')
                                        ->addActionLabel('Añadir plan')
                                        ->hidden(function (Get $get) {
                                            if ($get('plan') == 'CM') {
                                                return false;
                                            }

                                            return true;
                                        })
                                        ->schema([
                                            Radio::make('plan_id')
                                                ->label('Seleccione una opción y añadir plan:')

                                                ->inLine()
                                                ->live()
                                                ->options(function (Get $get) {
                                                    Log::info($get('plan'));

                                                    return Plan::cotizable()->pluck('description', 'id');
                                                })->columnSpan(3),
                                            Select::make('age_range_id')
                                                ->label('Rango de edad')
                                                ->placeholder('Rango de edad')
                                                ->options(fn (Get $get) => QuotablePlans::ageRangeOptions($get('plan_id')))
                                                ->live()
                                                ->searchable()
                                                ->prefixIcon('heroicon-s-globe-europe-africa')
                                                ->disableOptionWhen(function ($value, $state, Get $get) {
                                                    return collect($get('../*.age_range_id'))
                                                        ->reject(fn ($id) => $id == $state)
                                                        ->filter()
                                                        ->contains($value);
                                                })
                                                ->validationMessages([
                                                    'required' => 'Campo Requerido',
                                                ])
                                                ->preload(),
                                            TextInput::make('total_persons')
                                                ->label('Cantidad de personas')
                                                ->placeholder('Cantidad de personas')
                                                ->numeric(),
                                        ])->columns(2),
                                ]),
                        ]),
                ])
                    ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                <x-filament::button
                    type="submit"
                    size="sm"
                    wire:target="create" 
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-70 pointer-events-none"
                    class="min-w-28 justify-center bg-indigo-600 hover:bg-indigo-700 text-white" 
                >
                    {{-- Contenido NORMAL: Visible solo cuando NO está cargando --}}
                    <span wire:loading.remove wire:target="create">
                        Guardar y Finalizar
                    </span>

                    {{-- Contenido CARGANDO: Visible solo mientras está cargando --}}
                    <span wire:loading wire:target="create"class="flex items-center space-x-2">
                        <span>Calculando Cotización y Generando PDF...</span>
                    </span>
                </x-filament::button>
            BLADE)))
                    ->columnSpanFull(),
            ]);
    }
}
