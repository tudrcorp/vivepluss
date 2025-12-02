<?php

namespace App\Filament\Resources\CorporateQuoteRequests\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

use App\Models\City;
use App\Models\Agent;
use App\Models\State;
use App\Models\Agency;
use App\Models\Region;
use App\Models\Country;
use App\Models\AgeRange;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Auth;
use App\Models\CorporateQuoteRequest;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\Blade;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Section;
use App\Http\Controllers\UtilsController;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class CorporateQuoteRequestForm
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
                                ->description('Estás a punto de comenzar a crear una nueva cotización DRESS-TAYLOR, por favor ingresa la información del cliente para personalizarla. ¡Puede ver el avance del proceso en la barra de estatus!')
                                ->schema([
                                    Grid::make(4)
                                        ->schema([
                                            TextInput::make('code')
                                                ->label('Nro. de solicitud')
                                                ->prefixIcon('heroicon-m-clipboard-document-check')
                                                ->default(function () {
                                                    if (CorporateQuoteRequest::max('id') == null) {
                                                        $parte_entera = 0;
                                                    } else {
                                                        $parte_entera = CorporateQuoteRequest::max('id');
                                                    }
                                                    return 'SOL-CORP-000' . $parte_entera + 1;
                                                })
                                                ->disabled()
                                                ->dehydrated()
                                                ->maxLength(255),
                                            TextInput::make('poblation')
                                                ->label('Población / Nro de personas')
                                                ->numeric()
                                                ->prefixIcon('heroicon-m-user')
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
                                                    'required'  => 'Campo Requerido',
                                                ])
                                                ->hiddenOn('edit'),
                                            TextInput::make('phone')
                                                ->prefixIcon('heroicon-s-phone')
                                                ->tel()
                                                ->label('Número de teléfono')
                                                ->validationMessages([
                                                    'required'  => 'Campo Requerido',
                                                ])
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                                    $countryCode = $get('country_code');
                                                    if ($countryCode) {
                                                        $cleanNumber = ltrim(preg_replace('/[^0-9]/', '', $state), '0');
                                                        $set('phone', $countryCode . $cleanNumber);
                                                    }
                                                }),
                                            TextInput::make('email')
                                                ->label('Correo Electrónico')
                                                ->email()
                                                ->rule('regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/')
                                                ->validationMessages([
                                                    'required' => 'Campo requerido',
                                                    'email' => 'El correo no es valido',
                                                    'regex' => 'El correo no debe contener mayúsculas, espacios, ñ, ni caracteres especiales no permitidos.',
                                                ]),
                                            Select::make('country_id_ti')
                                                ->label('País')
                                                ->live()
                                                ->options(Country::all()->pluck('name', 'id'))
                                                ->searchable()
                                                ->prefixIcon('heroicon-s-globe-europe-africa')
                                                ->required()
                                                ->validationMessages([
                                                    'required'  => 'Campo Requerido',
                                                ])
                                                ->default(189)
                                                ->preload(),
                                            Select::make('state_id_ti')
                                                ->label('Estado')
                                                ->options(function (Get $get) {
                                                    return State::where('country_id', $get('country_id_ti'))->pluck('definition', 'id');
                                                })
                                                ->afterStateUpdated(function (Set $set, $state) {
                                                    $region_id = State::where('id', $state)->value('region_id');
                                                    $region = Region::where('id', $region_id)->value('definition');
                                                    $set('region_ti', $region);
                                                })
                                                ->live()
                                                ->searchable()
                                                ->prefixIcon('heroicon-s-globe-europe-africa')
                                                ->required()
                                                ->validationMessages([
                                                    'required'  => 'Campo Requerido',
                                                ])
                                                ->preload(),
                                            TextInput::make('region_ti')
                                                ->label('Región')
                                                ->prefixIcon('heroicon-m-map')
                                                ->disabled()
                                                ->dehydrated()
                                                ->maxLength(255),
                                            Select::make('city_id_ti')
                                                ->label('Ciudad')
                                                ->options(function (Get $get) {
                                                    return City::where('country_id', $get('country_id_ti'))->where('state_id', $get('state_id_ti'))->pluck('definition', 'id');
                                                })
                                                ->searchable()
                                                ->prefixIcon('heroicon-s-globe-europe-africa')
                                                ->required()
                                                ->validationMessages([
                                                    'required'  => 'Campo Requerido',
                                                ])
                                                ->preload(),
                                        ])->columnSpanFull(),
                                    Grid::make(1)
                                        ->schema([
                                            FileUpload::make('document_file')
                                                ->label('Archivo de la solicitud de cotización')
                                                ->directory('solicitudes-archivos')
                                                ->visibility('public')
                                                ->uploadingMessage('Cargando archivo...')
                                                ->helperText('Por favor, adjunte el archivo de la solicitud de cotización donde describa la población, y rango de edades en formato Pdf, Excel, Word, Csv.')

                                        ]),
                                    Grid::make(1)
                                        ->schema([
                                            Textarea::make('observations')
                                                ->label('Especificaciones de la cotización')
                                                ->helperText('Por favor, describa las especificaciones de la cotización de forma detallada del tipo de plan, beneficios, coberturas y rango de edades que debe estar asociados a la solicitud.')
                                                ->required()
                                                ->autosize()
                                        ])->columnSpanFull(),
                                    Hidden::make('status')->default('PRE-APROBADA'),
                                    Hidden::make('created_by')->default(Auth::user()->name),
                                    
                                    //Codigo de agencia que crea la solicitud
                                    Hidden::make('code_agency')->default(function () {
                                        if (Auth::user()->agency_type == 'GENERAL') {
                                            return Auth::user()->code_agency;
                                        }
                                        return Auth::user()->code_agency;
                                    }),

                                    //Calculo de OWNER_CODE segun la agencia que esta conectada o el agente que esta conectado
                                    Hidden::make('owner_code')->default(function () {
                                        
                                        //1.- Agencia GENERAL
                                        if (Auth::user()->agency_type == 'GENERAL') {
                                            $owner = Agency::where('code', Auth::user()->code_agency)->value('owner_code');
                                            return $owner;
                                        }

                                        //2.- Agencia MASTER
                                        if (Auth::user()->agency_type == 'MASTER') {
                                            return Auth::user()->code_agency;
                                        }

                                        //3.- Agente o Subagente
                                        if (Auth::user()->is_agent == 1 || Auth::user()->is_subagent == 1) {
                                            $agent = Agent::where('id', Auth::user()->agent_id)->value('owner_code');
                                            return $agent;
                                        }
                                        return null;
                                    }),

                                    //Si manejan agentes
                                    Hidden::make('agent_id')->default(function () {
                                        if (Auth::user()->is_agent == 1) {
                                            return Auth::user()->agent_id;
                                        }
                                        return null;
                                    }),
                                    
                                ])
                                ->columns(3)
                                ->columnSpanFull()
                        ]),
                ])
                    ->submitAction(new HtmlString(Blade::render(<<<BLADE
                    <x-filament::button
                        type="submit"
                        size="sm"
                    >
                        Crear Solicitud
                    </x-filament::button>
                BLADE)))
                    ->columnSpanFull(),
            ]);
    }
}