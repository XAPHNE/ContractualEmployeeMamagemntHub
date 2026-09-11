<?php

namespace App\Filament\Widgets;

use App\Models\EmployeeContribution;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class MonthlyContributionChartWidget extends ChartWidget
{
    use HasWidgetShield;

    protected ?string $heading = 'Monthly Employee Contributions & Active Staff';

    protected ?string $description = 'Monthly active contributing employees and total deposited contribution amount';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = null;

    public function mount(): void
    {
        $currentYear = now()->month >= 4 ? now()->year : now()->year - 1;
        $this->filter ??= $currentYear.'-'.substr((string) ($currentYear + 1), -2);

        parent::mount();
    }

    protected function getFilters(): ?array
    {
        $currentYear = now()->month >= 4 ? now()->year : now()->year - 1;

        $filters = [];

        // Financial Years (April to March)
        foreach (range(0, -2) as $offset) {
            $start = $currentYear + $offset;
            $fy = $start.'-'.substr((string) ($start + 1), -2);
            $filters[$fy] = "FY {$fy} (Apr–Mar)";
        }

        // Calendar Years (January to December)
        $cy = now()->year;
        $filters[(string) $cy] = "CY {$cy} (Jan–Dec)";
        $filters[(string) ($cy - 1)] = 'CY '.($cy - 1).' (Jan–Dec)';

        return $filters;
    }

    protected function getData(): array
    {
        $currentYear = now()->month >= 4 ? now()->year : now()->year - 1;
        $activeFilter = $this->filter ?: ($currentYear.'-'.substr((string) ($currentYear + 1), -2));

        if (str_contains($activeFilter, '-')) {
            $months = [
                4 => 'Apr',
                5 => 'May',
                6 => 'Jun',
                7 => 'Jul',
                8 => 'Aug',
                9 => 'Sep',
                10 => 'Oct',
                11 => 'Nov',
                12 => 'Dec',
                1 => 'Jan',
                2 => 'Feb',
                3 => 'Mar',
            ];

            $query = EmployeeContribution::query()->where('fin_year', $activeFilter);
        } else {
            $months = [
                1 => 'Jan',
                2 => 'Feb',
                3 => 'Mar',
                4 => 'Apr',
                5 => 'May',
                6 => 'Jun',
                7 => 'Jul',
                8 => 'Aug',
                9 => 'Sep',
                10 => 'Oct',
                11 => 'Nov',
                12 => 'Dec',
            ];

            $query = EmployeeContribution::query()->whereYear('contribution_date', (int) $activeFilter);
        }

        $activeEmpCounts = (clone $query)
            ->whereHas('employee', fn ($q) => $q->active())
            ->selectRaw('month, COUNT(DISTINCT employee_id) as count')
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $totalContributions = (clone $query)
            ->selectRaw('month, SUM(contribution_amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $labels = [];
        $activeEmployeesData = [];
        $contributionsData = [];

        foreach ($months as $monthNum => $monthLabel) {
            $labels[] = $monthLabel;
            $activeEmployeesData[] = (int) ($activeEmpCounts[$monthNum] ?? 0);
            $contributionsData[] = (float) ($totalContributions[$monthNum] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Active Employees',
                    'data' => $activeEmployeesData,
                    'yAxisID' => 'y',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => '#3b82f6',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Total Contribution (₹)',
                    'data' => $contributionsData,
                    'yAxisID' => 'y1',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    'borderColor' => '#10b981',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Active Employees',
                    ],
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                    'grid' => [
                        'drawOnChartArea' => true,
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Total Contribution (₹)',
                    ],
                    'beginAtZero' => true,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }
}
