<?php

use App\Filament\Widgets\MonthlyContributionChartWidget;
use App\Models\Employee;
use App\Models\EmployeeContribution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('monthly contribution chart widget renders with dual-axis datasets for financial year', function () {
    $employee = Employee::factory()->create([
        'active' => '1',
    ]);

    EmployeeContribution::factory()->create([
        'employee_id' => $employee->id,
        'fin_year' => '2026-27',
        'month' => 8,
        'contribution_amount' => 225.00,
        'contribution_date' => '2026-08-15',
    ]);

    $widget = Livewire::test(MonthlyContributionChartWidget::class, ['filter' => '2026-27']);

    $widget->assertSuccessful();

    $instance = $widget->instance();
    $data = invade($instance)->getData();

    expect($data['labels'])->toBe(['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'])
        ->and($data['datasets'])->toHaveCount(2)
        ->and($data['datasets'][0]['label'])->toBe('Active Employees')
        ->and($data['datasets'][0]['yAxisID'])->toBe('y')
        ->and($data['datasets'][0]['data'][4])->toBe(1) // Aug is 5th month in FY (index 4)
        ->and($data['datasets'][1]['label'])->toBe('Total Contribution (₹)')
        ->and($data['datasets'][1]['yAxisID'])->toBe('y1')
        ->and($data['datasets'][1]['data'][4])->toBe(225.0);
});

test('monthly contribution chart widget handles calendar year filtering', function () {
    $employee = Employee::factory()->create([
        'active' => '1',
    ]);

    EmployeeContribution::factory()->create([
        'employee_id' => $employee->id,
        'fin_year' => '2026-27',
        'month' => 8,
        'contribution_amount' => 500.00,
        'contribution_date' => '2026-08-15',
    ]);

    $widget = Livewire::test(MonthlyContributionChartWidget::class, ['filter' => '2026']);

    $widget->assertSuccessful();

    $instance = $widget->instance();
    $data = invade($instance)->getData();

    expect($data['labels'])->toBe(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'])
        ->and($data['datasets'][0]['data'][7])->toBe(1) // Aug is 8th month in CY (index 7)
        ->and($data['datasets'][1]['data'][7])->toBe(500.0);
});

test('chart widget defines dual y-axis scale options', function () {
    $widget = Livewire::test(MonthlyContributionChartWidget::class);
    $instance = $widget->instance();
    $options = invade($instance)->getOptions();

    expect($options['scales'])->toHaveKeys(['y', 'y1', 'x'])
        ->and($options['scales']['y']['position'])->toBe('left')
        ->and($options['scales']['y1']['position'])->toBe('right');
});
