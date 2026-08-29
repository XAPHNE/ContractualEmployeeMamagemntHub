<?php

namespace App\Listeners;

use App\Models\AuthenticationLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class LogAuthenticationEvent
{
    public function handleLogin(Login $event): void
    {
        AuthenticationLog::create([
            'authenticatable_type' => get_class($event->user),
            'authenticatable_id' => $event->user->getKey(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'login_at' => now(),
            'login_successful' => true,
        ]);
    }

    public function handleFailed(Failed $event): void
    {
        AuthenticationLog::create([
            'authenticatable_type' => $event->user ? get_class($event->user) : null,
            'authenticatable_id' => $event->user?->getKey(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'login_at' => now(),
            'login_successful' => false,
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            $log = AuthenticationLog::where('authenticatable_type', get_class($event->user))
                ->where('authenticatable_id', $event->user->getKey())
                ->whereNull('logout_at')
                ->latest('login_at')
                ->first();

            $log?->update(['logout_at' => now()]);
        }
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Failed::class => 'handleFailed',
            Logout::class => 'handleLogout',
        ];
    }
}
