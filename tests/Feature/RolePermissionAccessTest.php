<?php

use App\Filament\Pages\SystemSettings;
use App\Filament\Widgets\DdoDepartmentChartWidget;
use App\Filament\Widgets\DdoStatsOverviewWidget;
use App\Filament\Widgets\LatestDdosWidget;
use App\Filament\Widgets\WelcomeWidget;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permissions = [
        'View:SystemSettings',
        'View:WelcomeWidget',
        'View:DdoStatsOverviewWidget',
        'View:DdoDepartmentChartWidget',
        'View:LatestDdosWidget',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $superAdminRole->syncPermissions($permissions);

    Role::firstOrCreate(['name' => 'DDO', 'guard_name' => 'web']);
});

test('super admin has access to system settings page and all widgets', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    Filament::auth()->login($superAdmin);

    expect(SystemSettings::canAccess())->toBeTrue();
    expect(WelcomeWidget::canView())->toBeTrue();
    expect(DdoStatsOverviewWidget::canView())->toBeTrue();
    expect(DdoDepartmentChartWidget::canView())->toBeTrue();
    expect(LatestDdosWidget::canView())->toBeTrue();
});

test('ddo role without page or widget permissions cannot access system settings or shielded widgets', function () {
    $ddoUser = User::factory()->create();
    $ddoUser->assignRole('DDO');

    Filament::auth()->login($ddoUser);

    expect(SystemSettings::canAccess())->toBeFalse();
    expect(WelcomeWidget::canView())->toBeFalse();
    expect(DdoStatsOverviewWidget::canView())->toBeFalse();
    expect(DdoDepartmentChartWidget::canView())->toBeFalse();
    expect(LatestDdosWidget::canView())->toBeFalse();
});
