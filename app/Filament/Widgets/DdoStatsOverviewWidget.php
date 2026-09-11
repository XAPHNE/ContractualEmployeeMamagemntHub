<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\EmployeeContribution;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DdoStatsOverviewWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $lastMonth = now()->subMonth();
        $currentMonth = now();

        // 1. Total Active Employees
        $totalEmployees = Employee::active()->count();

        // 2. Last Month's Deposited Contribution
        $lastMonthTotal = (float) EmployeeContribution::where('month', $lastMonth->month)
            ->whereYear('contribution_date', $lastMonth->year)
            ->sum('contribution_amount');

        // 3. Pending Employees for Last Month (Active employees with no contribution recorded)
        $contributedEmployeeIds = EmployeeContribution::where('month', $lastMonth->month)
            ->whereYear('contribution_date', $lastMonth->year)
            ->pluck('employee_id');

        $pendingEmployeesCount = Employee::active()
            ->whereNotIn('id', $contributedEmployeeIds)
            ->count();

        // 4. Current Month's Deposited Contribution so far
        $currentMonthTotal = (float) EmployeeContribution::where('month', $currentMonth->month)
            ->whereYear('contribution_date', $currentMonth->year)
            ->sum('contribution_amount');

        return [
            Stat::make('Active Employees', number_format($totalEmployees))
                ->description('Registered contractual staff')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make($lastMonth->format('F').' Collection', '₹'.number_format($lastMonthTotal, 2))
                ->description('Total deposited last month')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pending ('.$lastMonth->format('M').')', number_format($pendingEmployeesCount).' Employees')
                ->description('Awaiting last month deposit')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingEmployeesCount > 0 ? 'danger' : 'success'),

            Stat::make($currentMonth->format('F').' So Far', '₹'.number_format($currentMonthTotal, 2))
                ->description('Current month deposits')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
        ];
    }
}
