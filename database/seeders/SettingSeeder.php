<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'app_name' => config('app.name', 'CEMH'),
            'allow_registration' => false,
            'allow_email_updates' => true,
            'allow_account_deletion' => false,
            'min_password_length' => 8,
            'max_password_length' => 32,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_number' => true,
            'require_special_characters' => false,
            'password_history_limit' => 3,
            'password_expiry_days' => 90,
            'force_2fa' => true,
            'allow_disabling_2fa' => false,
            'max_login_attempts' => 5,
            'login_lockout_hours' => 1,
            'max_2fa_resend_attempts' => 3,
        ];

        foreach ($defaults as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
