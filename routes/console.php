<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('documents:check-missing')->hourly();

// Trae los planes que Integracorp asignó a ViVEplus en la matriz de negociación
// para que queden cotizables sin cargarlos a mano.
Schedule::command('catalog:sync-assigned-plans')->hourly();
