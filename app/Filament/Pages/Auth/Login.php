<?php

namespace App\Filament\Pages\Auth;

use App\Models\Setting;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    protected function getPasswordFormComponent(): Component
    {
        $isForgotPasswordEnabled = filter_var(Setting::get('enable_forgot_password', true), FILTER_VALIDATE_BOOLEAN);

        $password = TextInput::make('password')
            ->label(__('filament-panels::auth/pages/login.form.password.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required();

        if ($isForgotPasswordEnabled && filament()->hasPasswordReset()) {
            $password->hint(new HtmlString(Blade::render('<x-filament::link :href="filament()->getRequestPasswordResetUrl()" tabindex="-1"> {{ __(\'filament-panels::auth/pages/login.actions.request_password_reset.label\') }}</x-filament::link>')));
        }

        return $password;
    }
}
