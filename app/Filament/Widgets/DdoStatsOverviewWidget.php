<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\EmployeeContribution;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DdoStatsOverviewWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $ddo = auth()->user()?->ddo;

        // 1. Total Active Employees (scoped to DDO if logged in)
        $employeeQuery = Employee::active()->when($ddo, fn ($q) => $q->where('ddo_id', $ddo->id));
        $totalEmployees = (clone $employeeQuery)->count();

        // 2. Identify the last contributed month & financial year
        $latestContribution = EmployeeContribution::query()
            ->when($ddo, fn ($q) => $q->whereHas('employee', fn ($eq) => $eq->where('ddo_id', $ddo->id)))
            ->latest('contribution_date')
            ->first();

        if ($latestContribution) {
            $contributedMonth = (int) $latestContribution->month;
            $contributedFinYear = $latestContribution->fin_year;
            $monthDate = Carbon::createFromDate(null, $contributedMonth, 1);
            $monthName = $monthDate->format('F');
            $monthShort = $monthDate->format('M');
        } else {
            $lastMonth = now()->subMonth();
            $contributedMonth = $lastMonth->month;
            $contributedFinYear = ($contributedMonth >= 4 ? $lastMonth->year : $lastMonth->year - 1).'-'.substr((string) (($contributedMonth >= 4 ? $lastMonth->year : $lastMonth->year - 1) + 1), -2);
            $monthDate = $lastMonth->copy()->startOfMonth();
            $monthName = $lastMonth->format('F');
            $monthShort = $lastMonth->format('M');
        }

        // 3. Deposited Contribution for the Last Contributed Month
        $contributionsQuery = EmployeeContribution::query()
            ->when($ddo, fn ($q) => $q->whereHas('employee', fn ($eq) => $eq->where('ddo_id', $ddo->id)))
            ->where('month', $contributedMonth)
            ->when($contributedFinYear, fn ($q) => $q->where('fin_year', $contributedFinYear));

        $lastContributedMonthTotal = (float) (clone $contributionsQuery)->sum('contribution_amount');

        // 4. Pending Employees & Amount for the Last Contributed Month
        $contributedEmployeeIds = (clone $contributionsQuery)->pluck('employee_id');
        $contributedMonthEndDate = $monthDate->copy()->endOfMonth();

        $pendingEmployeesCount = (clone $employeeQuery)
            ->whereNotIn('id', $contributedEmployeeIds)
            ->where(function ($q) use ($contributedMonthEndDate) {
                $q->whereNull('date_of_joining')
                    ->orWhere('date_of_joining', '<=', $contributedMonthEndDate);
            })
            ->count();

        $unitAmount = (float) ((clone $contributionsQuery)->value('contribution_amount') ?? 225.00);
        $pendingContributionAmount = $pendingEmployeesCount * $unitAmount;

        return [
            Stat::make('Active Employees', number_format($totalEmployees))
                ->description('Registered contractual staff')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make($monthName.' Contribution', '₹'.number_format($lastContributedMonthTotal, 2))
                ->description('Total deposited for last contributed month')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pending ('.$monthShort.')', '₹'.number_format($pendingContributionAmount, 2))
                ->description('Awaiting deposit for '.$monthName)
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingContributionAmount > 0 ? 'danger' : 'success'),

            Stat::make('Pending ('.$monthShort.')', number_format($pendingEmployeesCount).' Employees')
                ->description('Awaiting deposit for '.$monthName)
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingEmployeesCount > 0 ? 'danger' : 'success'),
        ];
    }
}
