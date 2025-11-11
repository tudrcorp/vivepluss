<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
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
            ->spa(hasPrefetching: true)
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