<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\View\View;

class Dashboard extends BaseDashboard
{
    public function getHeader(): ?View
    {
        return view('filament.pages.dashboard-header');
    }
}
