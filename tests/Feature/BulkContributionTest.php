<?php

namespace Tests\Feature;

use App\Filament\Resources\EmployeeContributions\Pages\ManageEmployeeContributions;
use App\Models\Ddo;
use App\Models\Employee;
use App\Models\EmployeeContribution;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkContributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_resolves_ddo_by_email(): void
    {
        $ddo = Ddo::factory()->create([
            'email' => 'officer@apgcl.org',
            'ddoName' => 'Ainul Haque',
        ]);

        $user = User::factory()->create([
            'email' => 'officer@apgcl.org',
            'name' => 'Ainul Haque',
        ]);

        $this->assertNotNull($user->ddo);
        $this->assertEquals($ddo->id, $user->ddo->id);
        $this->assertTrue($user->isDdo());
    }

    public function test_bulk_contribution_creates_records_for_active_employees(): void
    {
        $ddo = Ddo::factory()->create([
            'email' => 'ddo1@apgcl.org',
        ]);

        $user = User::factory()->create([
            'email' => 'ddo1@apgcl.org',
        ]);

        $activeEmployee1 = Employee::factory()->create([
            'ddo_id' => $ddo->id,
            'active' => 'true',
        ]);

        $activeEmployee2 = Employee::factory()->create([
            'ddo_id' => $ddo->id,
            'active' => 'TRUE',
        ]);

        $inactiveEmployee = Employee::factory()->create([
            'ddo_id' => $ddo->id,
            'active' => false,
        ]);

        $finYear = '2025-26';
        $month = 6;
        $amount = 225.0;
        $date = '2025-07-01';

        // Simulate bulk contribution entry logic
        $targetEmployees = Employee::whereIn('id', [
            $activeEmployee1->id,
            $activeEmployee2->id,
            $inactiveEmployee->id,
        ])->get();

        $created = 0;
        foreach ($targetEmployees as $emp) {
            if (! $emp->isActive()) {
                continue;
            }

            EmployeeContribution::create([
                'employee_id' => $emp->id,
                'fin_year' => $finYear,
                'month' => $month,
                'contribution_amount' => $amount,
                'contribution_date' => $date,
            ]);
            $created++;
        }

        $this->assertEquals(2, $created);
        $this->assertDatabaseHas('employee_contributions', [
            'employee_id' => $activeEmployee1->id,
            'fin_year' => $finYear,
            'month' => $month,
            'contribution_amount' => 225.00,
        ]);
        $this->assertDatabaseHas('employee_contributions', [
            'employee_id' => $activeEmployee2->id,
            'fin_year' => $finYear,
            'month' => $month,
            'contribution_amount' => 225.00,
        ]);
        $this->assertDatabaseMissing('employee_contributions', [
            'employee_id' => $inactiveEmployee->id,
        ]);
    }

    public function test_chronological_rule_prevents_past_or_duplicate_months_in_same_fin_year(): void
    {
        $employee = Employee::factory()->create([
            'active' => 'true',
        ]);

        // Existing contribution for Month 5
        EmployeeContribution::factory()->create([
            'employee_id' => $employee->id,
            'fin_year' => '2025-26',
            'month' => 5,
            'contribution_amount' => 225.00,
        ]);

        $latestMonth = $employee->contributions()->where('fin_year', '2025-26')->max('month');
        $this->assertEquals(5, $latestMonth);

        // Month 4 is past (ineligible)
        $month4Attempt = 4;
        $isMonth4Valid = $latestMonth === null || $month4Attempt > $latestMonth;
        $this->assertFalse($isMonth4Valid);

        // Month 5 is duplicate (ineligible)
        $month5Attempt = 5;
        $isMonth5Valid = $latestMonth === null || $month5Attempt > $latestMonth;
        $this->assertFalse($isMonth5Valid);

        // Month 6 is next in sequence (eligible)
        $month6Attempt = 6;
        $isMonth6Valid = $latestMonth === null || $month6Attempt > $latestMonth;
        $this->assertTrue($isMonth6Valid);
    }

    public function test_ddo_cannot_make_bulk_contribution_if_an_active_employee_missed_previous_month_in_same_fin_year(): void
    {
        Setting::set('allow_skipping_contribution_months', false);

        $ddo = Ddo::factory()->create();

        $emp1 = Employee::factory()->create([
            'ddo_id' => $ddo->id,
            'active' => true,
            'date_of_joining' => '2024-01-01',
        ]);

        $emp2 = Employee::factory()->create([
            'ddo_id' => $ddo->id,
            'active' => true,
            'date_of_joining' => '2024-01-01',
        ]);

        // Emp1 has contribution for Month 5
        EmployeeContribution::factory()->create([
            'employee_id' => $emp1->id,
            'fin_year' => '2025-26',
            'month' => 5,
            'contribution_amount' => 225.00,
        ]);

        // Emp2 missed Month 5!

        // Attempt Month 6
        $missed = ManageEmployeeContributions::getMissedPreviousMonthEmployees('2025-26', 6, $ddo->id);

        $this->assertCount(1, $missed);
        $this->assertEquals($emp2->id, $missed->first()->id);
    }

    public function test_month_4_checks_month_3_of_previous_financial_year(): void
    {
        Setting::set('allow_skipping_contribution_months', false);

        $ddo = Ddo::factory()->create();

        $emp = Employee::factory()->create([
            'ddo_id' => $ddo->id,
            'active' => true,
            'date_of_joining' => '2024-01-01',
        ]);

        // Previous FY (2024-25) Month 3 (March) is missing
        $missed = ManageEmployeeContributions::getMissedPreviousMonthEmployees('2025-26', 4, $ddo->id);
        $this->assertCount(1, $missed);
        $this->assertEquals($emp->id, $missed->first()->id);

        // Now record contribution for Month 3 in 2024-25
        EmployeeContribution::factory()->create([
            'employee_id' => $emp->id,
            'fin_year' => '2024-25',
            'month' => 3,
            'contribution_amount' => 225.00,
        ]);

        // Now Month 4 should pass with zero missed employees
        $missedAfter = ManageEmployeeContributions::getMissedPreviousMonthEmployees('2025-26', 4, $ddo->id);
        $this->assertCount(0, $missedAfter);
    }

    public function test_employee_who_joined_in_current_month_is_excluded_from_previous_month_check(): void
    {
        Setting::set('allow_skipping_contribution_months', false);

        $ddo = Ddo::factory()->create();

        // Existing employee with Month 5 contribution
        $existingEmp = Employee::factory()->create([
            'ddo_id' => $ddo->id,
            'active' => true,
            'date_of_joining' => '2024-01-01',
        ]);

        EmployeeContribution::factory()->create([
            'employee_id' => $existingEmp->id,
            'fin_year' => '2025-26',
            'month' => 5,
            'contribution_amount' => 225.00,
        ]);

        // New employee joining in Month 6 (June 2025)
        Employee::factory()->create([
            'ddo_id' => $ddo->id,
            'active' => true,
            'date_of_joining' => '2025-06-10',
        ]);

        // When checking Month 6 (June 2025), new hire must NOT block the DDO
        $missed = ManageEmployeeContributions::getMissedPreviousMonthEmployees('2025-26', 6, $ddo->id);
        $this->assertCount(0, $missed);
    }

    public function test_system_setting_allow_skipping_months_bypasses_the_check(): void
    {
        $ddo = Ddo::factory()->create();

        Employee::factory()->create([
            'ddo_id' => $ddo->id,
            'active' => true,
            'date_of_joining' => '2024-01-01',
        ]);

        // Employee missed Month 5, setting is FALSE -> blocked
        Setting::set('allow_skipping_contribution_months', false);
        $missedWhenDisabled = ManageEmployeeContributions::getMissedPreviousMonthEmployees('2025-26', 6, $ddo->id);
        $this->assertCount(1, $missedWhenDisabled);

        // Turn setting TRUE -> allowed to proceed
        Setting::set('allow_skipping_contribution_months', true);
        $missedWhenEnabled = ManageEmployeeContributions::getMissedPreviousMonthEmployees('2025-26', 6, $ddo->id);
        $this->assertCount(0, $missedWhenEnabled);
    }
}
