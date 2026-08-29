<?php

namespace App\Filament\Widgets;

use App\Models\Ddo;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DdoDepartmentChartWidget extends ChartWidget
{
    protected ?string $heading = 'Employees by Department';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $departmentCounts = Ddo::query()
            ->select('departmentName', DB::raw('count(*) as total'))
            ->groupBy('departmentName')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'departmentName')
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
