<?php

namespace App\Livewire;

use App\Models\Coverage;
use Livewire\Component;

/**
 * Tabla de tarifas de la propuesta económica para los planes que Integracorp
 * asigna a la empresa aliada.
 *
 * Mismo diseño que las de los planes históricos (Ideal/Especial), pero las
 * columnas de cobertura se arman con las coberturas reales del plan en vez de
 * estar escritas a mano, y cada celda se ubica buscando su `coverage_id` en
 * lugar de confiar en el orden en que vengan las filas.
 */
class PlanesCotizacionIndividualAsignado extends Component
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

        return view('livewire.planes-cotizacion-individual-asignado', [
            'coverages' => $coverages,
        ]);
    }
}
