<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Widgets\WelcomeWidget;
use App\Models\Setting;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $getAppName = fn (): string => (string) rescue(
            fn () => Setting::get('app_name') ?: config('app.name'),
            config('app.name')
        );

        return $panel
            ->default()
            ->brandLogo(fn () => new HtmlString('
                <div style="display: flex; align-items: center; gap: 0.75rem; height: 100%;">
                    <img src="'.asset('logo.png').'" alt="Logo" style="height: 1.5rem; max-height: 24px; width: auto; object-fit: contain; flex-shrink: 0;" />
                    <span style="font-size: 1.25rem; font-weight: 700; line-height: 1.5rem; letter-spacing: -0.025em; white-space: nowrap;">
                        '.e($getAppName()).'
                    </span>
                </div>
            '))
            ->brandLogoHeight('1.5rem')
            ->brandName($getAppName)
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->colors([
                'primary' => Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->profile(EditProfile::class, isSimple: false)
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->navigationGroups([
                'Management',
                'API Manager',
                'Roles & Permissions',
                'Audit Hub',
                'Settings',
            ])
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                WelcomeWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->multiFactorAuthentication([
                EmailAuthentication::make()
                    ->codeExpiryMinutes(10),
            ], isRequired: fn (): bool => rescue(fn () => filter_var(Setting::get('force_2fa', false), FILTER_VALIDATE_BOOLEAN), false))
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
