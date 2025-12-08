<?php

namespace App\Providers\Filament;

use Filament\Panel;
use App\Models\User;
use Livewire\Component;
use App\Services\GetColor;
use Filament\PanelProvider;
use Filament\Actions\Action;
use App\Models\Configuration;
use Filament\Pages\Dashboard;
use Filament\Support\Enums\Width;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\FilamentInfoWidget;
use App\Http\Middleware\DuplicatedSession;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use App\Filament\Resources\Agents\AgentResource;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use App\Filament\Resources\Agencies\AgencyResource;
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
            
        return $panel
            ->default()
            ->id('viveadmin')
            ->path('viveadmin')
            ->login()
            ->spa()
            ->passwordReset()
            ->colors( function (): array {

                $primaryColor   = Configuration::findOrFail(1)?->primaryColor ??  Color::Blue;
                $infoColor      = Configuration::findOrFail(1)?->infoColor ??     Color::Cyan;
                
                return [
                    'primary'   => $primaryColor,
                    'info'      => $infoColor
                ];
            })
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
                // 'profile' => fn(Action $action) => $action->label('Perfil General')
                //     ->icon('heroicon-o-user-circle')
                //     ->url(AgencyResource::getUrl('edit', ['record' => DB::table('agencies')->select('id')->where('code', Auth::user()->code_agency)->value('id')], panel: 'viveadmin')),
                //     // ->url(function (Component $livewire) {
                //     //     if(Auth::user()->is_agent == 1) {
                //     //         return AgentResource::getUrl('edit', ['record' => DB::table('agents')->select('id')->where('id', Auth::user()->agent_id)->value('id')], panel: 'viveadmin');
                //     //     } else {
                //     //         return AgencyResource::getUrl('edit', ['record' => DB::table('agencies')->select('id')->where('code', Auth::user()->code_agency)->value('id')], panel: 'viveadmin');
                //     //     }
                //     // }),
                
                Action::make('edit_configuration')
                    ->label('Configuración')
                    ->icon('heroicon-s-cog')
                    ->color('primary')
                    ->hidden(fn () => Auth::user()->is_whiteCompanyAdmin != 1 && Auth::user()->agency_type != 'MASTER')
                    ->url(function (Component $livewire) {
                        return ConfigurationResource::getUrl('edit', ['record' => Configuration::where('white_company_id', Auth::user()->white_company_id)->value('id')], panel: 'viveadmin');
                    }),
                // ...
                'logout' => fn(Action $action) => $action
                    ->label('Cerrar Sesión')
                    ->color('danger')
                    ->url(route('external')),

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
            ->viteTheme('resources/css/filament/viveadmin/theme.css')
            ->resourceCreatePageRedirect('index')
            ->resourceEditPageRedirect('index')
            ->authMiddleware([
                DuplicatedSession::class,
            ]);
    }
}