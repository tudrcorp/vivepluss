<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Flowframe\Trend\Trend;
use App\Models\Affiliation;
use App\Models\Configuration;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class VentasAgenciasChart extends ChartWidget
{
    protected ?string $heading = 'Ventas por Agencias';

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
                'code_agency',
                DB::raw('SUM(total_amount) as total_ventas_agencia')
            )
            ->where('owner_code', $ownerCode)
            ->groupBy('code_agency') // Solo agrupamos por la columna que queremos en el resultado (code_agency)
            ->get();
            


        return [
            'datasets' => [
                [
                    'label' => 'Ventas vs Plan',
                    'data' => $data->map(fn($data) => $data->total_ventas_agencia),
                    'backgroundColor' => $setting->infoColor,
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