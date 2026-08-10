<?php

namespace App\Filament\Widgets;

use App\Models\Configuration;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Js;

class VentasChart extends ChartWidget
{
    protected ?string $heading = 'Ventas del Año en Curso';

    protected ?string $maxHeight = '380px';

    protected bool $isCollapsible = true;

    protected int|string|array $columnSpan = 'full';

    private const MONTH_LABELS = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    public function getDescription(): ?string
    {
        return 'Suma del total_amount de afiliaciones activas (individuales y corporativas) mes a mes, en lo que va del año.';
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * Monthly ACTIVA total_amount sums for the given year. activated_at is a
     * "d/m/Y" varchar that's blank on some rows, so the effective date falls
     * back to created_at whenever activated_at can't be parsed.
     *
     * @return Collection<int, float>
     */
    private function monthlySales(string $table, string $activatedAtColumn, string $createdAtColumn, string $statusColumn, string $totalColumn, int $year, callable $scope): Collection
    {
        $effectiveDate = "COALESCE(STR_TO_DATE(NULLIF({$activatedAtColumn}, ''), '%d/%m/%Y'), {$createdAtColumn})";

        $query = DB::table($table)
            ->where($statusColumn, 'ACTIVA')
            ->whereRaw("YEAR({$effectiveDate}) = ?", [$year])
            ->selectRaw("MONTH({$effectiveDate}) as month, SUM({$totalColumn}) as total")
            ->groupBy('month');

        $scope($query);

        return $query->pluck('total', 'month');
    }

    protected function getData(): array
    {
        $whiteCompanyId = Configuration::currentWhiteCompanyId();
        $year = now()->year;
        $months = range(1, now()->month);

        $individualByMonth = $this->monthlySales(
            'affiliations',
            'activated_at',
            'created_at',
            'status',
            'total_amount',
            $year,
            fn ($query) => $query->where('white_company_id', $whiteCompanyId),
        );

        $corporateByMonth = $this->monthlySales(
            'affiliation_corporates as ac',
            'ac.activated_at',
            'ac.created_at',
            'ac.status',
            'ac.total_amount',
            $year,
            fn ($query) => $query->join('corporate_quotes as cq', 'ac.corporate_quote_id', '=', 'cq.id')
                ->where('cq.white_company_id', $whiteCompanyId),
        );

        return [
            'datasets' => [
                [
                    'label' => 'Afiliaciones Individuales',
                    'data' => collect($months)->map(fn (int $month) => round((float) ($individualByMonth[$month] ?? 0), 2))->all(),
                    'borderColor' => '#60a5fa',
                    'backgroundColor' => 'rgba(96, 165, 250, 0.15)',
                    'pointBackgroundColor' => '#60a5fa',
                    'tension' => 0.4,
                    'fill' => true,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 6,
                ],
                [
                    'label' => 'Afiliaciones Corporativas',
                    'data' => collect($months)->map(fn (int $month) => round((float) ($corporateByMonth[$month] ?? 0), 2))->all(),
                    'borderColor' => '#34d399',
                    'backgroundColor' => 'rgba(52, 211, 153, 0.15)',
                    'pointBackgroundColor' => '#34d399',
                    'tension' => 0.4,
                    'fill' => true,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 6,
                ],
            ],
            'labels' => collect($months)->map(fn (int $month) => self::MONTH_LABELS[$month - 1])->all(),
        ];
    }

    protected function getOptions(): RawJs
    {
        // Single-quoted and HTML-attribute-safe: this raw JS gets embedded inside the
        // `x-data="..."` double-quoted attribute, so it must never contain a literal `"`.
        $currencySymbol = Js::from(Configuration::currencySymbol());

        return RawJs::make(<<<JS
        {
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                    },
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function (context) {
                            const value = context.parsed.y ?? 0;
                            return context.dataset.label + ': ' + {$currencySymbol} + ' ' + value.toLocaleString('es-VE', { minimumFractionDigits: 2 });
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: {
                        display: true,
                    },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return {$currencySymbol} + ' ' + value.toLocaleString('es-VE');
                        },
                    },
                },
            },
        }
        JS);
    }
}
