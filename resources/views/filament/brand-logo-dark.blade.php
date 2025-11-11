@php
$setting = \App\Models\Configuration::first(); // o el modelo que uses
$logoUrl = $setting?->brandLogo
? asset('storage/' . $setting->brandLogo)
: asset('default-logo.png'); // Ruta al logo por defecto
@endphp

<img src="{{ $logoUrl }}" alt="Logo-dark" style="height: 100%; width: auto;" />
