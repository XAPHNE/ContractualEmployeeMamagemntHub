<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Ddo;
use App\Models\Employee;
use App\Models\EmployeeContribution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MmlsayApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mmlsay_api_requires_key(): void
    {
        $response = $this->getJson('/api/mmlsay/employee?pan=ABCDE1234F');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'code' => 'UNAUTHORIZED',
            ]);
    }

    public function test_mmlsay_api_validates_required_pan_parameter(): void
    {
        $apiKey = ApiKey::create([
            'name' => 'MMLSAY Test Portal',
            'key' => 'mmlsay_live_key_12345',
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/mmlsay/employee?Key={$apiKey->key}");

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'ERROR',
                'message' => "The 'pan' query parameter is required.",
            ]);
    }

    public function test_mmlsay_api_returns_correct_nested_json_payload(): void
    {
        $user = User::factory()->create();

        $apiKey = ApiKey::create([
            'name' => 'MMLSAY Integration Gateway',
            'key' => 'mmlsay_key_999999',
            'is_active' => true,
        ]);

        $ddo = Ddo::factory()->create([
            'ddoId' => '26226',
            'ddoName' => 'DDO/PRD/001',
            'departmentName' => 'Panchayat & Rural Development Department',
            'districtName' => 'Kamrup',
            'treasuryName' => 'Kamrup, Amingaon',
            'treasuryCode' => 'T-001',
            'officeName' => 'P&RD Directorate',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $employee = Employee::factory()->create([
            'pan' => 'ABCDE1234F',
            'full_Name' => 'Pooja Sharma',
            'first_Name' => 'Pooja',
            'last_Name' => 'Sharma',
            'type' => 'EMPLOYEE',
            'mobile' => '9876543210',
            'ddo_id' => $ddo->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        EmployeeContribution::factory()->create([
            'employee_id' => $employee->id,
            'month' => 10,
            'fin_year' => '2024-25',
            'contribution_amount' => 225.00,
            'contribution_date' => '2024-10-30',
        ]);

        EmployeeContribution::factory()->create([
            'employee_id' => $employee->id,
            'month' => 6,
            'fin_year' => '2025-26',
            'contribution_amount' => 225.00,
            'contribution_date' => '2025-07-01',
        ]);

        $response = $this->getJson("/api/mmlsay/employee?Key={$apiKey->key}&pan=ABCDE1234F&type=EMPLOYEE&month=6&finYear=2025-26");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'profile' => [
                    'id',
                    'full_Name',
                    'first_Name',
                    'middle_Name',
                    'last_Name',
                    'type',
                    'mobile',
                    'employee_code',
                    'pan',
                    'gender',
                    'dob',
                    'designation',
                    'grade',
                    'pay_band',
                    'grade_pay',
                    'date_of_joining',
                    'dor',
                    'gpf_nps',
                    'email',
                    'present_address',
                    'permanent_address',
                    'district',
                    'active',
                ],
                'bank' => [
                    'ac_number',
                    'ac_type',
                    'ac_name',
                    'ac_bank',
                    'ac_branch',
                    'ac_ifsc',
                ],
                'ddo' => [
                    'department',
                    'department_id',
                    'department_district',
                    'treasury',
                    'treasury_id',
                    'ddo',
                    'ddo_id',
                    'office_name',
                ],
                'contribution_info' => [
                    'contribution_amount',
                    'contribution_started_date',
                    'last_contribution_date',
                    'finYear',
                    'month',
                    'total_contribution',
                    'message',
                ],
                'contribution_history' => [
                    '*' => [
                        'month',
                        'contribution_amount',
                        'contribution_date',
                        'finYear',
                    ],
                ],
                'status',
            ])
            ->assertJson([
                'status' => 'SUCCESS',
                'profile' => [
                    'pan' => 'ABCDE1234F',
                    'full_Name' => 'Pooja Sharma',
                ],
                'ddo' => [
                    'ddo_id' => '26226',
                    'department' => 'Panchayat & Rural Development Department',
                ],
                'contribution_info' => [
                    'contribution_amount' => 225.0,
                    'total_contribution' => 450.0,
                    'finYear' => '2025-26',
                    'month' => '6',
                    'message' => 'SUCCESSFUL',
                ],
            ]);
    }

    public function test_mmlsay_api_returns_404_for_unknown_pan(): void
    {
        $apiKey = ApiKey::create([
            'name' => 'MMLSAY Gateway',
            'key' => 'mmlsay_key_000000',
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/mmlsay/employee?Key={$apiKey->key}&pan=UNKNOWNPAN99");

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'ERROR',
                'message' => "Employee record with PAN 'UNKNOWNPAN99' was not found.",
            ]);
    }
}
