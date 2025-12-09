<?php

namespace App\Filament\Widgets;

use App\Models\Configuration;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class VentasAgentesChart extends ChartWidget
{
    protected ?string $heading = 'Ventas por Agentes';

    protected ?string $maxHeight = '350px';

    protected bool $isCollapsible = true;

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

    public function getDescription(): ?string
    {
        return 'Creadas por el agente en el período seleccionado';
    }

    protected function getData(): array
    {

        $setting = Configuration::first();

        $ownerCode = 'TDG-153'; // Asegúrate de que esta variable esté definida correctamente

        $data = DB::table('affiliations')
                ->select(
                    'agent_id',
                    DB::raw('SUM(total_amount) as total_ventas_agente')
                )
                ->where('owner_code', $ownerCode)
                ->where('agent_id', '!=', null)
                ->groupBy('agent_id') // Solo agrupamos por la columna que queremos en el resultado (code_agency)
                ->get();



        return [
            'datasets' => [
                [
                    'label' => 'Ventas vs Plan',
                    'data' => $data->map(fn($data) => $data->total_ventas_agente),
                    'backgroundColor' => $setting->primaryColor,
                    'borderColor' => $setting->infoColor,
                    'fill' => true,
                ],
            ],
            // 'labels' => ($data->map(fn(TrendValue $value) => Carbon::parse($value->created_at)->isoFormat('DD-MMM'))->toArray()),
            'labels' => ($data->map(fn($data) => $data->code_agency)),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}