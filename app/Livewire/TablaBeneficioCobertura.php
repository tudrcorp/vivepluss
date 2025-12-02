<?php

namespace App\Livewire;

use App\Models\Plan;
use Livewire\Component;
use App\Models\Coverage;
use App\Models\BenefitPlan;
use App\Models\Configuration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;

class TablaBeneficioCobertura extends Component
{
    public $planId;

    public function mount($id)
    {
        $this->planId = $id;
    }

    public function tablaBeneficiosCoberturas()
    {

        try {

            $planId = $this->planId;

            $icon = new HtmlString('<i class="fas fa-heart text-red-500"></i>');

            if (!$planId) {
                return [
                    'coverages' => new Collection(),
                    'benefits' => new Collection(),
                    'matrix' => [],
                ];
            }

            // 1. Obtener las Coberturas asociadas al Plan (Headers de la tabla)
            $coverages = Coverage::where('plan_id', $planId)->orderBy('price', 'asc')
                ->get(['id', 'price'])
                ->keyBy('id');

            // Calcular el ancho restante para las columnas de cobertura
            $totalCoverages = count($coverages);
            // 70% del ancho de la tabla dividido entre el número de coberturas.
            $coverageColumnWidth = $totalCoverages > 0 ? (70 / $totalCoverages) : 0;


            // 2. Obtener todos los Beneficios (Filas de la tabla)
            $benefits = BenefitPlan::where('plan_id', $planId)->get(['benefit_id', 'description']);

            // 3. Obtener los datos del pivot (limite_uso) para las coberturas de este plan
            $pivotData = DB::table('benefit_coverages')
                ->select('benefit_id', 'coverage_id', 'limit')
                ->whereIn('coverage_id', $coverages->keys())
                ->get();

            // 4. Construir la matriz pivote
            $matrix = [];

            foreach ($benefits as $benefit) {
                $matrix[$benefit->benefit_id] = [
                    'nombre' => $benefit->description,
                    'limits' => [],
                ];

                foreach ($coverages as $coverage) {
                    $limitRecord = $pivotData->first(
                        fn($item) => $item->benefit_id == $benefit->benefit_id && $item->coverage_id == $coverage->id
                    );

                    // Lógica modificada: Si el registro del límite NO existe, se asigna '✅' en lugar de 'N/A'
                    // Si el registro SÍ existe, se usa el límite real ($limitRecord->limit)
                    $matrix[$benefit->benefit_id]['limits'][$coverage->id] = $limitRecord ? $limitRecord->limit : 'N/A';
                }
            }

            return [
                'coverages' => $coverages,
                'benefits' => $benefits,
                'matrix' => $matrix,
                'coverageColumnWidth' => $coverageColumnWidth,
            ];
        } catch (\Throwable $th) {
            Log::error('Error al calcular edades: ' . $th->getMessage());
            // Retornar estructura vacía en caso de error
            return [
                'coverages' => new Collection(),
                'benefits' => new Collection(),
                'matrix' => [],
                'coverageColumnWidth' => 0,
            ];
        }
    }

    public function render()
    {
        $data = $this->tablaBeneficiosCoberturas();

        $coverageColumnWidth = $data['coverageColumnWidth'];
        $coverages = $data['coverages'];
        $benefits = $data['benefits'];
        $matrix = $data['matrix'];

        $config = Configuration::first();

        $colorPrimary = $config->primaryColor ?? '#1f2937'; // Fallback
        $colorInfo = $config->infoColor ?? '#3b82f6'; // Fallback

        return view('livewire.tabla-beneficio-cobertura', compact('coverageColumnWidth', 'coverages', 'benefits', 'matrix', 'colorPrimary', 'colorInfo'));
    }
}