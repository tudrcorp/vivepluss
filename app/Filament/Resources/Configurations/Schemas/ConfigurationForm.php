<?php

namespace App\Filament\Resources\Configurations\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ToggleButtons;

class ConfigurationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Configuracion Brand Logo')
                    ->schema([
                        Grid::make(2)->schema([
                            FileUpload::make('brandLogo')
                                ->directory('logo')
                                ->visibility('public'),
                            
                        ])->columnSpanFull(),
                        Grid::make(2)->schema([
                            Select::make('brandLogoHeight')
                            ->label('Tamaño del Logo')
                                ->options([
                                    '1rem'  => '1rem',
                                    '2rem'  => '2rem',
                                    '3rem'  => '3rem',
                                    '4rem'  => '4rem',
                                    '5rem'  => '5rem',
                                    '6rem'  => '6rem',
                                    '7rem'  => '7rem',
                                    '8rem'  => '8rem',
                                    '9rem'  => '9rem',
                                    '10rem' => '10rem',
                                ])
                                ->helperText('Establece el tamaño del logo ubicado en la barra de navegación. Se recomienda un tamaño no mayor 5rem'),
                            
                        ])->columnSpanFull(),
                    ])->columnSpanFull(),
                Fieldset::make('Colores para el Tema')
                    ->schema([
                        ColorPicker::make('primaryColor')
                            ->default('#A13DDB')
                            ->regex('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})\b$/'),
                        ColorPicker::make('infoColor')
                            ->default('#3B82F6')
                            ->regex('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})\b$/'),
                        
                    ])->columnSpanFull(),
                Fieldset::make('Pagina Web')
                    ->schema([
                        Fieldset::make('Información SEO')    
                            ->schema([
                                TextInput::make('web_headTitle')
                                    ->label('Título de la Pagina Web')
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('web_headDescription')
                                    ->label('Descripción de la Pagina Web')
                                    ->required()
                                    ->autosize(),
                                TextInput::make('web_headKeywords')
                                    ->label('Palabras Claves de la Pagina Web')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('web_headOpTitle')
                                    ->label('Título de la Pagina Web para Autor Principal(OP)')
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('web_headOpDescription')
                                    ->label('Descripción de la Pagina Web para Autor Principal(OP)')
                                    ->required()
                                    ->autosize(),
                                TextInput::make('web_headXTitle')
                                    ->label('Palabras Claves de la Pagina Web para X(Twitter)')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('web_headXDescription')
                                    ->label('Descripción de la Pagina Web para X(Twitter)')
                                    ->required()
                                    ->maxLength(255),
                            ])->columnSpanFull(),
                            
                        Fieldset::make('Seccion Principal')
                            ->schema([
                                Grid::make(2)->schema([
                                    FileUpload::make('web_headerLogo')
                                        ->label('Logo de la Pagina Web (Esquina Superior Derecha)')
                                        ->directory('web-images')
                                        ->visibility('public')
                                        ->required()
                                ])->columnSpanFull(),
                                TextInput::make('web_sectionOne_title')
                                    ->label('Título principal de la Sección')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('web_sectionOne_title_ln_2')
                                    ->label('Linea 2 del Título principal de la Sección')
                                    ->required()
                                    ->helperText('Esta línea aparecerá justo debajo del título principal. Sino desea usarla, puede dejarla en blanco.')
                                    ->maxLength(255),
                                Select::make('web_icons_redSocial')
                                    ->label('Lista de Redes Sociales (Esquina Inferior Izquierda)')
                                    ->options([
                                        'fab fa-facebook-f'     => 'Facebook',
                                        'fab fa-instagram'      => 'Instagram',
                                        'fab fa-twitter'        => 'Twitter',
                                        'fab fa-whatsapp'       => 'Linkedin',
                                    ])
                                    ->multiple(),
                            ])->columnSpanFull(),
                            
                        Fieldset::make('Seccion Nosotros!')
                            ->schema([
                                TextInput::make('web_nosotrosTitle_parteIzquierda')
                                    ->label('Título principal parte Izquierda')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('web_nosotrosTitle_parteDerecha')
                                    ->label('Título principal parte Derecha')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('web_nosotros')
                                    ->label('Texto principal Nosotros!')
                                    ->required()
                            ])->columnSpanFull(),

                        Fieldset::make('Seccion Mision y Visión')
                            ->schema([
                                Grid::make(2)->schema([
                                    FileUpload::make('web_imageMision')
                                        ->label('Imagen Misión')
                                        ->directory('web-images')
                                        ->visibility('public')
                                        ->required(),
                                    FileUpload::make('web_imageVision')
                                        ->label('Imagen Visión')
                                        ->directory('web-images')
                                        ->visibility('public')
                                        ->required(),
                                ])->columnSpanFull(),
                                TextInput::make('web_mision')
                                    ->label('Texto principal Misión')
                                    ->required(),
                                TextInput::make('web_vision')
                                    ->label('Texto principal Visión')
                                    ->required(),
                            ])->columnSpanFull(),
                            
                        Fieldset::make('Seccion Planes')
                            ->schema([
                                TextInput::make('web_plansTitle')
                                    ->label('Título')
                                    ->required(),
                                TextInput::make('web_plansSubTitle')
                                    ->label('Sub Título')
                                    ->required(),
                                Grid::make(3)->schema([
                                    Fieldset::make('Plan Uno')
                                        ->schema([
                                            FileUpload::make('web_imagePlan_1')
                                                ->label('Imagen Plan')
                                                ->directory('web-images')
                                                ->visibility('public')
                                                ->required(),
                                            TextInput::make('web_namePlan_1')
                                                ->label('Nombre del Plan')
                                                ->required(),
                                            // TextInput::make('web_pricePlan_1')
                                            //     ->label('Precio del Plan')
                                            //     ->required(),
                                            TextInput::make('web_descriptionPlan_1')
                                                ->label('Descripción corta')
                                                ->required(),
                                            // TextInput::make('web_formaPagoPlan_1')
                                            //     ->label('Forma de Pago (Frecuencia, año, mes, trimestre, semana, etc.)')
                                            //     ->required(),
                                            // TextInput::make('web_descriptionPricePlan_1')
                                            //     ->label('Descripción corta para la forma de pago')
                                            //     ->required(),
                                            // TextInput::make('web_descriptionBottonPlan_1')
                                            //     ->label('Boton de compra')
                                            //     ->required(),
                                            TextInput::make('web_Plan_1_items_1')
                                                ->label('Item 1')
                                                ->required(),
                                            TextInput::make('web_Plan_1_items_2')
                                                ->label('Item 2')
                                                ->required(),
                                            TextInput::make('web_Plan_1_items_3')
                                                ->label('Item 3')
                                                ->required(),
                                            TextInput::make('web_Plan_1_items_4')
                                                ->label('Item 4')
                                                ->required(),
                                        ])->columns(1),
                                    Fieldset::make('Plan Dos')
                                        ->schema([
                                            FileUpload::make('web_imagePlan_2')
                                                ->label('Imagen Plan')
                                                ->directory('web-images')
                                                ->visibility('public')
                                                ->required(),
                                            TextInput::make('web_namePlan_2')
                                                ->label('Nombre del Plan')
                                                ->required(),
                                            // TextInput::make('web_pricePlan_1')
                                            //     ->label('Precio del Plan')
                                            //     ->required(),
                                            TextInput::make('web_descriptionPlan_2')
                                                ->label('Descripción corta')
                                                ->required(),
                                            // TextInput::make('web_formaPagoPlan_1')
                                            //     ->label('Forma de Pago (Frecuencia, año, mes, trimestre, semana, etc.)')
                                            //     ->required(),
                                            // TextInput::make('web_descriptionPricePlan_1')
                                            //     ->label('Descripción corta para la forma de pago')
                                            //     ->required(),
                                            // TextInput::make('web_descriptionBottonPlan_1')
                                            //     ->label('Boton de compra')
                                            //     ->required(),
                                            TextInput::make('web_Plan_2_items_1')
                                                ->label('Item 1')
                                                ->required(),
                                            TextInput::make('web_Plan_2_items_2')
                                                ->label('Item 2')
                                                ->required(),
                                            TextInput::make('web_Plan_2_items_3')
                                                ->label('Item 3')
                                                ->required(),
                                            TextInput::make('web_Plan_2_items_4')
                                                ->label('Item 4')
                                                ->required(),
                                        ])->columns(1),
                                    Fieldset::make('Plan Tres')
                                        ->schema([
                                            FileUpload::make('web_imagePlan_3')
                                                ->label('Imagen Plan')
                                                ->directory('web-images')
                                                ->visibility('public')
                                                ->required(),
                                            TextInput::make('web_namePlan_3')
                                                ->label('Nombre del Plan')
                                                ->required(),
                                            // TextInput::make('web_pricePlan_1')
                                            //     ->label('Precio del Plan')
                                            //     ->required(),
                                            TextInput::make('web_descriptionPlan_3')
                                                ->label('Descripción corta')
                                                ->required(),
                                            // TextInput::make('web_formaPagoPlan_1')
                                            //     ->label('Forma de Pago (Frecuencia, año, mes, trimestre, semana, etc.)')
                                            //     ->required(),
                                            // TextInput::make('web_descriptionPricePlan_1')
                                            //     ->label('Descripción corta para la forma de pago')
                                            //     ->required(),
                                            // TextInput::make('web_descriptionBottonPlan_1')
                                            //     ->label('Boton de compra')
                                            //     ->required(),
                                            TextInput::make('web_Plan_3_items_1')
                                                ->label('Item 1')
                                                ->required(),
                                            TextInput::make('web_Plan_3_items_2')
                                                ->label('Item 2')
                                                ->required(),
                                            TextInput::make('web_Plan_3_items_3')
                                                ->label('Item 3')
                                                ->required(),
                                            TextInput::make('web_Plan_3_items_4')
                                                ->label('Item 4')
                                                ->required(),
                                        ])->columns(1),
                                    TextInput::make('web_footerPlans')
                                        ->label('Footer de la seccion de Planes')
                                        ->required(),
                                    TextInput::make('web_footerBottonPlans')
                                        ->label('Botón Footer de la sección de Planes')
                                        ->required(),
                                ])->columnSpanFull(),
                            ])->columnSpanFull(),

                        Fieldset::make('Sección Ubicación y Google Maps!')
                        ->schema([
                            TextInput::make('web_ubicacionTitle')
                                ->label('Título')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('web_ubicacionSubTitle')
                                ->label('Sub Título')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('web_ubicacionUrl')
                                ->label('URL del Mapa Embebido de Google Maps')
                                ->required(),
                            TextInput::make('web_ubicacionDireccion')
                                ->label('Dirección Principal')
                                ->required(),
                            TextInput::make('web_ubicacionHorarios')
                                ->label('Horario de Atención')
                                ->required()
                        ])->columnSpanFull(),

                        Fieldset::make('Footer Web')
                            ->schema([
                                Grid::make(2)->schema([
                                    FileUpload::make('web_footerLogo')
                                        ->label('Logo Footer')
                                        ->directory('web-images')
                                        ->visibility('public')
                                        ->required()
                                ])->columnSpanFull(),
                                TextInput::make('web_footerLogoText')
                                    ->label('Texto Logo Footer (Ubicado debajo del logo)')
                                    ->required()
                                    ->maxLength(255),
                            ])->columnSpanFull(),
                            
                    ])->columnSpanFull(),
                Fieldset::make('Configuracion de Headers(Titulos) y Subtitulos de las Tablas del Sistema')
                    ->schema([
                        Fieldset::make('Tabla Afiliaciones Corporativas')
                            ->schema([
                                TextInput::make('table_af_corp_table_title')
                                    ->label('Título(Header)')
                                    ->helperText('Si este campo esta vació, el sistema mostrara el titulo por defecto')
                                    ->maxLength(255),
                                TextInput::make('table_af_corp_table_description')
                                    ->label('Subtitulo o Descripción')
                                    ->helperText('Si este campo esta vació, el sistema mostrara el titulo por defecto')
                                    ->maxLength(255),
                            ]),
                        Fieldset::make('Tabla Afiliaciones Individuales')
                            ->schema([
                                TextInput::make('table_af_ind_table_title')
                                    ->label('Título(Header)')
                                    ->helperText('Si este campo esta vació, el sistema mostrara el titulo por defecto')
                                    ->maxLength(255),
                                TextInput::make('table_af_ind_table_description')
                                    ->label('Subtitulo o Descripción')
                                    ->helperText('Si este campo esta vació, el sistema mostrara el titulo por defecto')
                                    ->maxLength(255),
                            ]),
                        Fieldset::make('Tabla Cotizaciones Corporativas')
                            ->schema([
                                TextInput::make('table_quote_corp_table_title')
                                    ->label('Título(Header)')
                                    ->helperText('Si este campo esta vació, el sistema mostrara el titulo por defecto')
                                    ->maxLength(255),
                                TextInput::make('table_quote_corp_table_description')
                                    ->label('Subtitulo o Descripción')
                                    ->helperText('Si este campo esta vació, el sistema mostrara el titulo por defecto')
                                    ->maxLength(255),
                            ]),
                        Fieldset::make('Tabla Cotizaciones Individuales')
                            ->schema([
                                TextInput::make('table_quote_ind_table_title')
                                    ->label('Título(Header)')
                                    ->helperText('Si este campo esta vació, el sistema mostrara el titulo por defecto')
                                    ->maxLength(255),
                                TextInput::make('table_quote_ind_table_description')
                                    ->label('Subtitulo o Descripción')
                                    ->helperText('Si este campo esta vació, el sistema mostrara el titulo por defecto')
                                    ->maxLength(255),
                            ]),
                        Fieldset::make('Tabla Solicitudes')
                            ->schema([
                                TextInput::make('table_request_table_title')
                                    ->label('Título(Header)')
                                    ->helperText('Si este campo esta vació, el sistema mostrara el titulo por defecto')
                                    ->maxLength(255),
                                TextInput::make('table_request_table_description')
                                    ->label('Subtitulo o Descripción')
                                    ->helperText('Si este campo esta vació, el sistema mostrara el titulo por defecto')
                                    ->maxLength(255),
                            ]),
                        Fieldset::make('Tabla Agencia Generales')
                            ->schema([
                                TextInput::make('table_agency_title')
                                    ->label('Título(Header)')
                                    ->helperText('Si este campo esta vació, el sistema mostrara el titulo por defecto')
                                    ->maxLength(255),
                                TextInput::make('table_agency_description')
                                    ->label('Subtitulo o Descripción')
                                    ->helperText('Si este campo esta vació, el sistema mostrara el titulo por defecto')
                                    ->maxLength(255),
                            ]),

                    ])->columnSpanFull(),
                    
                Fieldset::make('Estructura de Agentes')
                    ->schema([
                        ToggleButtons::make('agents_module_enabled')
                            ->label('Posse estructura de Agentes?')
                            ->boolean()
                            ->inline()
                            ->helperText('Si esta opción está desactivada, el sistema no mostrará ninguna funcionalidad relacionada con agentes.'),
                    ])->columnSpanFull(),

                Fieldset::make('Seguridad del Sistema')
                    ->schema([
                        Radio::make('duplicatedSession')
                            ->label('Manejo de Sesiones de Usuarios')
                            ->options([
                                1 => 'Restringir Sesiones Duplicadas',
                                0 => 'Permitir Sesiones Duplicadas',
                            ])
                            ->descriptions([
                                1 => 'El sistema no permite que el usuario tenga dos sesiones abiertas al mismo tiempo.',
                                0 => 'El sistema permite que el usuario tenga dos sesiones abiertas al mismo tiempo.',
                            ])
                    ])->columnSpanFull(),
            ]);
    }
}