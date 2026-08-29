<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Password;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : $value]
        );
    }

    public static function getPasswordRules(): Password
    {
        $rules = Password::min((int) (self::get('min_password_length', 8)));

        if (self::get('require_uppercase', false)) {
            $rules->mixedCase();
        }

        if (self::get('require_number', false)) {
            $rules->numbers();
        }

        if (self::get('require_special_characters', false)) {
            $rules->symbols();
        }

        return $rules;
    }
}
