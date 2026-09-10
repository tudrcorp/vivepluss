<?php

namespace App\Livewire;

use App\Models\Coverage;
use Livewire\Component;

/**
 * Tabla de tarifas de la propuesta corporativa para los planes que Integracorp
 * asigna a la empresa aliada. Mismo diseño que las de los planes históricos,
 * con las columnas armadas desde las coberturas reales del plan.
 */
class PlanesCotizacionCorporativaAsignado extends Component
{
    public $data = [];

    public $name;

    public $name_user;

    public $planId;

    public function mount($data, $name, $name_user, $planId)
    {
        $this->data = $data;
        $this->name = $name;
        $this->name_user = $name_user;
        $this->planId = $planId;
    }

    public function render()
    {
        $coverages = Coverage::query()
            ->where('plan_id', $this->planId)
            ->orderBy('price')
            ->get(['id', 'price']);

        return view('livewire.planes-cotizacion-corporativa-asignado', [
            'coverages' => $coverages,
        ]);
    }
}
