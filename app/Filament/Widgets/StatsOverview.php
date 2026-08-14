<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\IndividualQuotes\IndividualQuoteResource;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\Configuration;
use App\Models\CorporateQuote;
use App\Models\CreditReconciliation;
use App\Models\IndividualQuote;
use Filament\Widgets\Widget;

class StatsOverview extends Widget
{
    protected string $view = 'filament.widgets.stats-overview';

    protected int|string|array $columnSpan = 'full';

    public function getDescription(): ?string
    {
        return 'Estadísticas generales de la plataforma.';
    }

    /**
     * Compares how many records were created this month vs. the previous month,
     * so the analyst can read growth/decline at a glance without leaving the widget.
     *
     * @return array{current: int, previous: int, direction: 'up'|'down'|'flat'|'new', label: string}
     */
    private function monthOverMonth(string $modelClass, int|string|null $whiteCompanyId): array
    {
        $now = now();
        $startOfThisMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $startOfThisMonth->copy()->subMonthNoOverflow();
        $endOfLastMonth = $startOfThisMonth->copy()->subSecond();

        $current = $modelClass::where('white_company_id', $whiteCompanyId)
            ->whereBetween('created_at', [$startOfThisMonth, $now])
            ->count();

        $previous = $modelClass::where('white_company_id', $whiteCompanyId)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->count();

        return [
            'current' => $current,
            'previous' => $previous,
            ...$this->changeSummary($current, $previous),
        ];
    }

    /**
     * @return array{direction: 'up'|'down'|'flat'|'new', label: string}
     */
    private function changeSummary(int $current, int $previous): array
    {
        if ($previous === 0) {
            return $current === 0
                ? ['direction' => 'flat', 'label' => '0%']
                : ['direction' => 'new', 'label' => 'Nuevo'];
        }

        $change = (int) round((($current - $previous) / $previous) * 100);

        return match (true) {
            $change > 0 => ['direction' => 'up', 'label' => "+{$change}%"],
            $change < 0 => ['direction' => 'down', 'label' => "{$change}%"],
            default => ['direction' => 'flat', 'label' => '0%'],
        };
    }

    /**
     * Monthly counts (Jan–Dec) for the current calendar year, so the widget can
     * chart the metric's behavior across the year at a glance.
     *
     * @return array<int, int>
     */
    private function monthlySeries(string $modelClass, int|string|null $whiteCompanyId): array
    {
        $countsByMonth = $modelClass::where('white_company_id', $whiteCompanyId)
            ->whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        return collect(range(1, 12))
            ->map(fn (int $month) => (int) ($countsByMonth[$month] ?? 0))
            ->all();
    }

    /**
     * @var array<int, string>
     */
    private const MONTH_LABELS = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    /**
     * Crédito disponible de la empresa (marca blanca) actual: lo asignado por
     * Integracorp en `white_companies.assigned_credit` menos lo ya consumido en
     * pagos a crédito (`credit_reconciliations`), para que el analista vea de
     * primera mano cuánto le queda, no el total original fijo.
     */
    public function getAssignedCredit(): string
    {
        $whiteCompanyId = Configuration::currentWhiteCompanyId();

        $availableCredit = CreditReconciliation::remainingCredit($whiteCompanyId);

        return number_format($availableCredit, 2, ',', '.');
    }

    public function getCurrencySymbol(): string
    {
        return Configuration::currencySymbol();
    }

    public function getStats(): array
    {
        $whiteCompanyId = Configuration::currentWhiteCompanyId();

        return array_map(
            fn (array $stat) => [
                ...$stat,
                'chart' => $this->buildSparkline($stat['points']),
                'monthLabels' => self::MONTH_LABELS,
            ],
            [
                [
                    'label' => 'Agencias',
                    'sublabel' => 'TOTAL AGENCIAS',
                    'value' => (string) Agency::where('white_company_id', $whiteCompanyId)->count(),
                    'icon' => 'heroicon-o-building-office-2',
                    'accent' => '#60a5fa',
                    'points' => $this->monthlySeries(Agency::class, $whiteCompanyId),
                    'comparison' => $this->monthOverMonth(Agency::class, $whiteCompanyId),
                ],
                [
                    'label' => 'Agentes',
                    'sublabel' => 'TOTAL AGENTES',
                    'value' => (string) Agent::where('white_company_id', $whiteCompanyId)->count(),
                    'icon' => 'heroicon-o-user-group',
                    'accent' => '#a78bfa',
                    'points' => $this->monthlySeries(Agent::class, $whiteCompanyId),
                    'comparison' => $this->monthOverMonth(Agent::class, $whiteCompanyId),
                ],
                [
                    'label' => 'Cotizaciones Individuales',
                    'sublabel' => 'COTIZACIONES INDIVIDUALES',
                    'value' => (string) IndividualQuote::where('white_company_id', $whiteCompanyId)->count(),
                    'icon' => 'heroicon-o-document-text',
                    'accent' => '#fbbf24',
                    'points' => $this->monthlySeries(IndividualQuote::class, $whiteCompanyId),
                    'comparison' => $this->monthOverMonth(IndividualQuote::class, $whiteCompanyId),
                    'action' => [
                        'label' => 'Crear cotización individual',
                        'url' => IndividualQuoteResource::getUrl('create'),
                    ],
                ],
                [
                    'label' => 'Cotizaciones Corporativas',
                    'sublabel' => 'COTIZACIONES CORPORATIVAS',
                    'value' => (string) CorporateQuote::where('white_company_id', $whiteCompanyId)->count(),
                    'icon' => 'heroicon-o-building-library',
                    'accent' => '#34d399',
                    'points' => $this->monthlySeries(CorporateQuote::class, $whiteCompanyId),
                    'comparison' => $this->monthOverMonth(CorporateQuote::class, $whiteCompanyId),
                ],
            ],
        );
    }

    /**
     * Builds a smooth SVG sparkline (line + filled area) for a set of points,
     * plus per-point coordinates (as % of the chart box) so each point can be
     * turned into an interactive hover target with its own tooltip.
     *
     * @param  array<int, float>  $points
     * @return array{line: string, area: string, peak: array{x: float, y: float}, width: float, height: float, points: array<int, array{leftPct: float, topPct: float, value: float}>}
     */
    private function buildSparkline(array $points, float $width = 280, float $height = 84, float $padTop = 10, float $padBottom = 6): array
    {
        $count = count($points);
        $min = min($points);
        $max = max($points);
        $range = ($max - $min) ?: 1;
        $usableHeight = $height - $padTop - $padBottom;
        $step = $count > 1 ? $width / ($count - 1) : 0;

        $coords = [];
        foreach ($points as $i => $value) {
            $coords[] = [
                'x' => round($i * $step, 2),
                'y' => round($padTop + $usableHeight * (1 - (($value - $min) / $range)), 2),
            ];
        }

        $line = "M {$coords[0]['x']} {$coords[0]['y']}";
        for ($i = 1; $i < $count; $i++) {
            $prev = $coords[$i - 1];
            $cur = $coords[$i];
            $cpx = round(($prev['x'] + $cur['x']) / 2, 2);
            $line .= " C {$cpx} {$prev['y']}, {$cpx} {$cur['y']}, {$cur['x']} {$cur['y']}";
        }

        $peakIndex = array_search($max, $points, true);

        return [
            'line' => $line,
            'area' => "{$line} L {$width} {$height} L 0 {$height} Z",
            'peak' => $coords[$peakIndex],
            'width' => $width,
            'height' => $height,
            'points' => array_map(
                fn (array $coord, float $value) => [
                    'leftPct' => round(($coord['x'] / $width) * 100, 2),
                    'topPct' => round(($coord['y'] / $height) * 100, 2),
                    'value' => $value,
                ],
                $coords,
                $points,
            ),
        ];
    }
}
