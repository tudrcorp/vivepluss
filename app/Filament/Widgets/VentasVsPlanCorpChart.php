<?php

namespace App\Filament\Widgets;

use App\Models\Configuration;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class VentasVsPlanCorpChart extends ChartWidget
{
    protected ?string $heading = 'Ventas Vs Planes Corporativos';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '350px';

    protected bool $isCollapsible = true;

    public function getDescription(): ?string
    {
        return 'Creadas por el agente en el período seleccionado';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }

    protected function getData(): array
    {

        $setting = Configuration::first();

        $data = DB::table('affiliation_corporates as ac')
            // 1. UNIÓN con affiliate_plans (para obtener el plan_id y la relación corporativa)
            ->join('afilliation_corporate_plans as ap', 'ac.id', '=', 'ap.affiliation_corporate_id')

            // 2. NUEVA UNIÓN con la tabla plans (para obtener el nombre del plan)
            // Se une 'ap.plan_id' (el ID del plan) con 'p.id' (la clave primaria de la tabla plans)
            ->join('plans as p', 'ap.plan_id', '=', 'p.id')

            // Selecciona la suma de 'total_amount', el 'plan_id' y el 'name' del plan
            ->select(
                DB::raw('SUM(ac.total_amount) as quantity'),
                'ap.plan_id',
                'p.description as plan_name' // Alias para claridad en el resultado
            )
            ->where('owner_code', auth()->user()->code_agency)

            // Agrupa los resultados por el ID y el nombre del plan para asegurar la consistencia.
            ->groupBy('ap.plan_id', 'p.description')

            // Obtiene el resultado final
            ->get();
        // dd($data);

        $planes = DB::connection('mysql_vivepluss')
            ->table('plans')
            ->select('id', 'description')
            // Pluck crea un array asociativo con 'id' como clave y 'description' como valor
            ->pluck('description', 'id');

        foreach ($data as $key => $value) {
            $data[$key]->plan_id = $planes[$value->plan_id];
        }


        return [
            'datasets' => [
                [
                    'label' => 'Ventas vs Plan',
                    'data' => $data->map(fn($data) => $data->quantity),
                    'backgroundColor' => $setting->infoColor,
                    'borderColor' => $setting->infoColor,
                    'fill' => true,
                ],
            ],
            'labels' => ($data->map(fn($data) => $data->plan_name)),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}