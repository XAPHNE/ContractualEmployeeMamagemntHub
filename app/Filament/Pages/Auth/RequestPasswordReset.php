<?php

namespace App\Filament\Pages\Auth;

use App\Models\Setting;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    public function mount(): void
    {
        if (! filter_var(Setting::get('enable_forgot_password', true), FILTER_VALIDATE_BOOLEAN)) {
            abort(404, 'Password reset is disabled.');
        }

        parent::mount();
    }

    public function request(): void
    {
        if (! filter_var(Setting::get('enable_forgot_password', true), FILTER_VALIDATE_BOOLEAN)) {
            abort(404, 'Password reset is disabled.');
        }

        parent::request();
    }

    /**
     * Compute rate limiting key based on configured strategy (email, ip, or email_and_ip).
     */
    protected function getRateLimitKey($method, $component = null)
    {
        $component ??= static::class;
        $strategy = (string) Setting::get('forgot_password_throttle_by', 'email_and_ip');

        $rawState = rescue(fn () => $this->form->getRawState(), [], false);
        $email = strtolower(trim((string) ($rawState['email'] ?? $this->data['email'] ?? '')));
        $ip = (string) request()->ip();

        $identifier = match ($strategy) {
            'email' => 'email:'.($email ?: 'empty'),
            'ip' => 'ip:'.$ip,
            'email_and_ip' => 'email_ip:'.($email ?: 'empty').'|'.$ip,
            default => 'email_ip:'.($email ?: 'empty').'|'.$ip,
        };

        return 'livewire-rate-limiter:'.sha1($component.'|'.$method.'|'.$identifier);
    }

    /**
     * Override rateLimit to use configured max attempts and method name.
     */
    protected function rateLimit($maxAttempts, $decaySeconds = 60, $method = null, $component = null)
    {
        $configuredMaxAttempts = (int) Setting::get('forgot_password_max_attempts', 3);
        $method ??= 'request';

        parent::rateLimit($configuredMaxAttempts, $decaySeconds, $method, $component);
    }

    /**
     * Public helper to test rate limiting directly.
     *
     * @throws TooManyRequestsException
     */
    public function executeRateLimit(): void
    {
        $this->rateLimit(2, 60, 'request');
    }
}
