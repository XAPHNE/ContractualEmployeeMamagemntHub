<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Ddo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DdoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_requires_api_key(): void
    {
        $response = $this->getJson('/api/ddos');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'code' => 'UNAUTHORIZED',
            ]);
    }

    public function test_api_authenticates_with_valid_key_and_returns_envelope(): void
    {
        $user = User::factory()->create();
        $apiKey = ApiKey::create([
            'name' => 'SAP Test Gateway',
            'key' => 'cemh_live_test_key_12345',
            'is_active' => true,
        ]);

        Ddo::factory()->count(3)->create([
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->withHeader('X-API-KEY', $apiKey->key)
            ->getJson('/api/ddos?per_page=2');

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
                        'ddoId',
                        'ddoName',
                        'pan',
                        'departmentName',
                        'directorate',
                        'postName',
                        'officeName',
                        'officeAddress',
                        'mobileNumber',
                        'treasuryName',
                        'treasuryCode',
                        'email',
                        'districtName',
                    ],
                ],
            ]);
    }

    public function test_api_supports_delta_sync(): void
    {
        $apiKey = ApiKey::create([
            'name' => 'SAP Delta Test Gateway',
            'key' => 'cemh_live_delta_key_99999',
            'is_active' => true,
        ]);

        $response = $this->withHeader('X-API-KEY', $apiKey->key)
            ->getJson('/api/ddos?updated_since=2026-01-01T00:00:00Z');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }
}
