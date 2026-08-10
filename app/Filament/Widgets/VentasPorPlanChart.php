<?php

namespace App\Filament\Widgets;

use App\Models\Configuration;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Js;

class VentasPorPlanChart extends ChartWidget
{
    protected ?string $heading = 'Ventas por Tipo de Plan';

    protected ?string $maxHeight = '380px';

    protected bool $isCollapsible = true;

    protected int|string|array $columnSpan = 'full';

    private const MONTH_LABELS = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    private const PLAN_COLORS = ['#60a5fa', '#a78bfa', '#fbbf24', '#34d399', '#f472b6'];

    public function getDescription(): ?string
    {
        return 'Suma del total_amount de afiliaciones activas (individuales y corporativas) por plan, mes a mes, en lo que va del año.';
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * Monthly ACTIVA total_amount sums per plan_id for the given year.
     * activated_at is a "d/m/Y" varchar that's blank on some rows, so the
     * effective date falls back to created_at whenever it can't be parsed.
     *
     * @return Collection<int, object{plan_id: int, month: int, total: float}>
     */
    private function monthlyByPlan(string $table, string $activatedAtColumn, string $createdAtColumn, string $statusColumn, string $totalColumn, string $planIdColumn, int $year, callable $scope): Collection
    {
        $effectiveDate = "COALESCE(STR_TO_DATE(NULLIF({$activatedAtColumn}, ''), '%d/%m/%Y'), {$createdAtColumn})";

        $query = DB::table($table)
            ->where($statusColumn, 'ACTIVA')
            ->whereRaw("YEAR({$effectiveDate}) = ?", [$year])
            ->selectRaw("{$planIdColumn} as plan_id, MONTH({$effectiveDate}) as month, SUM({$totalColumn}) as total")
            ->groupBy(DB::raw($planIdColumn), DB::raw("MONTH({$effectiveDate})"));

        $scope($query);

        return $query->get();
    }

    protected function getData(): array
    {
        $whiteCompanyId = Configuration::currentWhiteCompanyId();
        $year = now()->year;
        $months = range(1, now()->month);

        $individualRows = $this->monthlyByPlan(
            'affiliations',
            'activated_at',
            'created_at',
            'status',
            'total_amount',
            'plan_id',
            $year,
            fn ($query) => $query->where('white_company_id', $whiteCompanyId),
        );

        $corporateRows = $this->monthlyByPlan(
            'affiliation_corporates as ac',
            'ac.activated_at',
            'ac.created_at',
            'ac.status',
            'ac.total_amount',
            'ap.plan_id',
            $year,
            fn ($query) => $query->join('afilliation_corporate_plans as ap', 'ac.id', '=', 'ap.affiliation_corporate_id')
                ->join('corporate_quotes as cq', 'ac.corporate_quote_id', '=', 'cq.id')
                ->where('cq.white_company_id', $whiteCompanyId),
        );

        $totalsByPlan = [];

        foreach ($individualRows->concat($corporateRows) as $row) {
            $totalsByPlan[$row->plan_id][(int) $row->month] = ($totalsByPlan[$row->plan_id][(int) $row->month] ?? 0) + (float) $row->total;
        }

        $plans = DB::connection('mysql_vivepluss')
            ->table('plans')
            ->select('id', 'description')
            ->orderBy('id')
            ->get();

        return [
            'datasets' => $plans->values()->map(fn ($plan, int $i) => [
                'label' => $plan->description,
                'data' => collect($months)->map(fn (int $month) => round($totalsByPlan[$plan->id][$month] ?? 0, 2))->all(),
                'borderColor' => self::PLAN_COLORS[$i % count(self::PLAN_COLORS)],
                'pointBackgroundColor' => self::PLAN_COLORS[$i % count(self::PLAN_COLORS)],
                'tension' => 0.4,
                'fill' => false,
                'pointRadius' => 3,
                'pointHoverRadius' => 6,
            ])->all(),
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
