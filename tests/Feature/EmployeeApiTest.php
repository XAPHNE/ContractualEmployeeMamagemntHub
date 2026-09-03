<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_api_requires_api_key(): void
    {
        $response = $this->getJson('/api/employees');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'code' => 'UNAUTHORIZED',
            ]);
    }

    public function test_employee_api_authenticates_and_returns_data_envelope(): void
    {
        $user = User::factory()->create();
        $apiKey = ApiKey::create([
            'name' => 'Govt Assam Health Insurance Gateway',
            'key' => 'cemh_assamscheme_live_key_77777',
            'is_active' => true,
        ]);

        Employee::factory()->count(3)->create([
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->withHeader('X-API-KEY', $apiKey->key)
            ->getJson('/api/employees?per_page=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'count',
                'total',
                'pagination' => [
                    'current_page',
                    'per_page',
                    'total_pages',
                    'has_more_pages',
                ],
                'data' => [
                    '*' => [
                        'id',
                        'emp_id',
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
                        'pincode',
                        'district',
                        'active',
                        'ac_number',
                        'ac_type',
                        'ac_name',
                        'ac_bank',
                        'ac_branch',
                        'ac_ifsc',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_employee_api_supports_delta_sync(): void
    {
        $apiKey = ApiKey::create([
            'name' => 'Govt Assam Delta Sync Gateway',
            'key' => 'cemh_assamscheme_delta_key_88888',
            'is_active' => true,
        ]);

        $response = $this->withHeader('X-API-KEY', $apiKey->key)
            ->getJson('/api/employees?updated_since=2026-01-01T00:00:00Z');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    public function test_employee_api_supports_single_record_retrieval(): void
    {
        $user = User::factory()->create();
        $apiKey = ApiKey::create([
            'name' => 'Govt Assam Single Lookup Gateway',
            'key' => 'cemh_assamscheme_single_key_99999',
            'is_active' => true,
        ]);

        $employee = Employee::factory()->create([
            'emp_id' => 'EMP990011',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->withHeader('X-API-KEY', $apiKey->key)
            ->getJson('/api/employees/EMP990011');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'count' => 1,
                'data' => [
                    'emp_id' => 'EMP990011',
                ],
            ]);
    }

    public function test_employee_api_returns_404_for_non_existent_record(): void
    {
        $apiKey = ApiKey::create([
            'name' => 'Govt Assam 404 Test Gateway',
            'key' => 'cemh_assamscheme_404_key_00000',
            'is_active' => true,
        ]);

        $response = $this->withHeader('X-API-KEY', $apiKey->key)
            ->getJson('/api/employees/NONEXISTENT999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'code' => 'NOT_FOUND',
            ]);
    }
}
