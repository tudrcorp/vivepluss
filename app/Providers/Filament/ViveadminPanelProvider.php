<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Livewire\Component;
use Filament\PanelProvider;
use Filament\Actions\Action;
use App\Models\Configuration;
use Filament\Pages\Dashboard;
use Filament\Support\Enums\Width;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use App\Filament\Resources\Configurations\ConfigurationResource;

class ViveadminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {

        //colors primaryColor, infoColor, grayColor dinamically
        $primaryColor   = Configuration::first()?->primaryColor ??  '#A13DDB';
        $infoColor      = Configuration::first()?->infoColor ??     '#3B82F6';
            
        return $panel
            ->default()
            ->id('viveadmin')
            ->path('viveadmin')
            ->login()
            ->spa()
            ->passwordReset()
            ->colors([
                'primary'   => $primaryColor,
                'info'      => $infoColor,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->userMenuItems([
                // 'profile' => fn(Action $action) => $action->label('Configuración')
                    Action::make('edit_configuration')
                    ->label('Configuración')
                    ->icon('heroicon-s-cog')
                    ->color('primary')
                    ->hidden(fn () => !auth()->user()->agency_type == 'MASTER')
                    ->url(function (Component $livewire) {
                        return ConfigurationResource::getUrl('edit', ['record' => 1], panel: 'viveadmin');
                    }),
                // ...
                // 'logout' => fn(Action $action) => $action
                //     ->label('Cerrar Sesión')
                //     ->color('danger')
                //     ->url(route('external')),
                // // ...
                // // ...
            ])
            ->databaseNotifications()
            ->databaseTransactions()
            ->sidebarCollapsibleOnDesktop()
            ->favicon(asset('images/ViveplussBlanco.png'))
            ->brandLogo(fn () => view('filament.brand-logo'))
            ->brandLogoHeight(fn () => Configuration::first()?->brandLogoHeight ?? '5rem')
            ->breadcrumbs(false)
            ->maxContentWidth(Width::Full)
            ->font('Quicksand')
            ->viteTheme('resources/css/filament/viveadmin/theme.css');
    }
}