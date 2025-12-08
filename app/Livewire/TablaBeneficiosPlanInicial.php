<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Plan;
use App\Models\Coverage;
use App\Models\BenefitPlan;
use App\Models\Configuration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;

class TablaBeneficiosPlanInicial extends Component
{
    public $planId = 1;

    public function tablaBeneficiosCoberturas()
    {

        try {

            $planId = $this->planId;

            if (!$planId) {
                return [
                    'benefits' => new Collection(),
                ];
            }

            // 2. Obtener todos los Beneficios (Filas de la tabla)
            $benefits = BenefitPlan::where('plan_id', $planId)->get();

            return $benefits;
            
        } catch (\Throwable $th) {
            
            Log::error('Error al calcular edades: ' . $th->getMessage());
            // Retornar estructura vacía en caso de error
            return [
                'benefits' => new Collection(),
            ];
        }
    }

    public function render()
    {
        $benefits = $this->tablaBeneficiosCoberturas();

        $config = Configuration::first();

        $colorPrimary = $config->primaryColor ?? '#1f2937'; // Fallback
        $colorInfo = $config->infoColor ?? '#3b82f6'; // Fallback

        return view('livewire.tabla-beneficios-plan-inicial', compact('benefits', 'colorPrimary', 'colorInfo'));
    }
}