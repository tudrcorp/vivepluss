@php
    $setting = \App\Models\Configuration::first(); // o el modelo que uses
    $logoUrl = $setting?->brandLogo
        ? asset('storage/' . $setting->brandLogo)
        : asset('default-logo.png'); // Ruta al logo por defecto
@endphp

<img src="{{ $logoUrl }}" alt="Brand Logo" style="height: 100%; width: auto; padding: 4px" />


