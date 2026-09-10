<?php

namespace App\Filament\Widgets;

use App\Models\Ddo;
use App\Models\Department;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DdoStatsOverviewWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalDdos = Ddo::count();
        $totalDepartments = Department::count();
        $totalDistricts = Ddo::distinct('districtName')->count('districtName');
        $totalUsers = User::count();

        return [
            Stat::make('Total Employees / DDOs', number_format($totalDdos))
                ->description('Registered records in system')
                ->descriptionIcon('heroicon-m-identification')
                ->chart([7, 10, 14, 18, 22, 28, $totalDdos])
                ->color('primary'),

            Stat::make('Departments', number_format($totalDepartments))
                ->description('Active departments')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),

            Stat::make('Districts Covered', number_format($totalDistricts))
                ->description('Districts with assigned DDOs')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('warning'),

            Stat::make('System Users', number_format($totalUsers))
                ->description('Authorized portal accounts')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }
}
