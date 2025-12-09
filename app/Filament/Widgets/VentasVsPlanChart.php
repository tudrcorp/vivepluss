<?php

namespace App\Filament\Widgets;

use App\Models\Configuration;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class VentasVsPlanChart extends ChartWidget
{
    protected ?string $heading = 'Ventas Vs Planes Individuales';

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

        $data = DB::table('affiliations')
            ->select(DB::raw('SUM(total_amount) as quantity, plan_id'))
            ->where('owner_code', auth()->user()->code_agency)
            ->groupBy('plan_id')
            ->get(); 

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
            'labels' => ($data->map(fn($data) => $data->plan_id)),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}