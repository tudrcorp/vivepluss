<?php

namespace App\Filament\Resources\Configurations\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\ColorPicker;

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
                                        TextInput::make('web_namePlan_1')
                                            ->label('Nombre del Plan')
                                            ->required(),
                                        TextInput::make('web_pricePlan_1')
                                            ->label('Precio del Plan')
                                            ->required(),
                                        TextInput::make('web_descriptionPlan_1')
                                            ->label('Descripción corta')
                                            ->required(),
                                        TextInput::make('web_formaPagoPlan_1')
                                            ->label('Forma de Pago (Frecuencia, año, mes, trimestre, semana, etc.)')
                                            ->required(),
                                        TextInput::make('web_descriptionPricePlan_1')
                                            ->label('Descripción corta para la forma de pago')
                                            ->required(),
                                        TextInput::make('web_descriptionBottonPlan_1')
                                            ->label('Boton de compra')
                                            ->required(),
                                    ])->columns(1),
                                Fieldset::make('Plan Dos')
                                    ->schema([
                                        TextInput::make('web_namePlan_2')
                                            ->label('Nombre del Plan')
                                            ->required(),
                                        TextInput::make('web_pricePlan_2')
                                            ->label('Precio del Plan')
                                            ->required(),
                                        TextInput::make('web_descriptionPlan_2')
                                            ->label('Descripción corta')
                                            ->required(),
                                        TextInput::make('web_formaPagoPlan_2')
                                            ->label('Forma de Pago (Frecuencia, año, mes, trimestre, semana, etc.)')
                                            ->required(),
                                        TextInput::make('web_descriptionPricePlan_2')
                                            ->label('Descripción corta para la forma de pago')
                                            ->required(),
                                        TextInput::make('web_descriptionBottonPlan_2')
                                            ->label('Boton de compra')
                                            ->required(),
                                    ])->columns(1),
                                Fieldset::make('Plan Tres')
                                    ->schema([
                                        TextInput::make('web_namePlan_3')
                                            ->label('Nombre del Plan')
                                            ->required(),
                                        TextInput::make('web_pricePlan_3')
                                            ->label('Precio del Plan')
                                            ->required(),
                                        TextInput::make('web_descriptionPlan_3')
                                            ->label('Descripción corta')
                                            ->required(),
                                        TextInput::make('web_formaPagoPlan_3')
                                            ->label('Forma de Pago (Frecuencia, año, mes, trimestre, semana, etc.)')
                                            ->required(),
                                        TextInput::make('web_descriptionPricePlan_3')
                                            ->label('Descripción corta para la forma de pago')
                                            ->required(),
                                        TextInput::make('web_descriptionBottonPlan_3')
                                            ->label('Boton de compra')
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
                        
                ])->columnSpanFull(),              
            ]);
    }
}