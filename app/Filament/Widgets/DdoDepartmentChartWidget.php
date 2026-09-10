<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class DdoDepartmentChartWidget extends ChartWidget
{
    use HasWidgetShield;

    protected ?string $heading = 'Employees by Department';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $departmentCounts = Department::withCount('ddos')
            ->having('ddos_count', '>', 0)
            ->orderByDesc('ddos_count')
            ->limit(10)
            ->pluck('ddos_count', 'name')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Employees / DDOs',
                    'data' => array_values($departmentCounts),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(139, 92, 246, 0.7)',
                        'rgba(236, 72, 153, 0.7)',
                        'rgba(20, 184, 166, 0.7)',
                        'rgba(249, 115, 22, 0.7)',
                        'rgba(99, 102, 241, 0.7)',
                        'rgba(107, 114, 128, 0.7)',
                    ],
                    'borderRadius' => 6,
                ],
            ],
            'labels' => array_keys($departmentCounts),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
