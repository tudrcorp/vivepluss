<?php

use Livewire\Volt\Volt;
use Laravel\Fortify\Features;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::post('/external', function () {
    Filament::auth()->logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->to(config('parametros.REDIRECT_LOGOUT_EXTERNAL_URL'));
})->name('external');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Volt::route('/agent/c/{code?}', 'agentFormCreate')->name('volt.agent.create');
Volt::route('/agency/c/{code?}', 'agencyFormCreate')->name('volt.agency.create');

//Ruta para crear la estructura de una agencia master
Volt::route('/m/o/c/{code?}', 'agencyMasterCreate')->name('master.organization.create');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});