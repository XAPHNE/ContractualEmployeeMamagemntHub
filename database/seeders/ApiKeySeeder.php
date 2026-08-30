<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use Illuminate\Database\Seeder;

class ApiKeySeeder extends Seeder
{
    public function run(): void
    {
        if (ApiKey::count() === 0) {
            ApiKey::create([
                'name' => 'SAP ERP Payment Processing Gateway',
                'key' => 'cemh_live_sap_' . bin2hex(random_bytes(16)),
                'rate_limit_per_minute' => 120,
                'is_active' => true,
            ]);
        }
    }
}
