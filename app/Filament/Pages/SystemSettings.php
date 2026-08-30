<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SystemSettings extends Page
{
    protected static UnitEnum | string | null $navigationGroup = 'Settings';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'System Settings';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.system-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'app_name' => Setting::get('app_name', config('app.name', 'CEMH')),
            'allow_registration' => (bool) Setting::get('allow_registration', false),
            'allow_email_updates' => (bool) Setting::get('allow_email_updates', true),
            'allow_account_deletion' => (bool) Setting::get('allow_account_deletion', false),

            'min_password_length' => (int) Setting::get('min_password_length', 8),
            'max_password_length' => (int) Setting::get('max_password_length', 32),
            'require_uppercase' => (bool) Setting::get('require_uppercase', true),
            'require_lowercase' => (bool) Setting::get('require_lowercase', true),
            'require_number' => (bool) Setting::get('require_number', true),
            'require_special_characters' => (bool) Setting::get('require_special_characters', false),
            'password_history_limit' => (int) Setting::get('password_history_limit', 3),
            'password_expiry_days' => (int) Setting::get('password_expiry_days', 90),

            'force_2fa' => (bool) Setting::get('force_2fa', true),
            'allow_disabling_2fa' => (bool) Setting::get('allow_disabling_2fa', false),
            'max_login_attempts' => (int) Setting::get('max_login_attempts', 5),
            'login_lockout_hours' => (int) Setting::get('login_lockout_hours', 1),
            'max_2fa_resend_attempts' => (int) Setting::get('max_2fa_resend_attempts', 3),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        Tab::make('General')
                            ->icon(Heroicon::OutlinedComputerDesktop)
                            ->schema([
                                Section::make('Application Preferences')
                                    ->description('Core application identity and member access policies.')
                                    ->schema([
                                        TextInput::make('app_name')
                                            ->label('Application Name')
                                            ->required()
                                            ->maxLength(255),
                                        Toggle::make('allow_registration')
                                            ->label('Allow Public User Registration')
                                            ->helperText('Enable or disable self-registration of new user accounts.')
                                            ->default(false),
                                        Toggle::make('allow_email_updates')
                                            ->label('Allow Email Address Modification')
                                            ->helperText('Permit users to change their own email address in profile.')
                                            ->default(true),
                                        Toggle::make('allow_account_deletion')
                                            ->label('Allow Account Self-Deletion')
                                            ->helperText('Permit users to delete their own accounts.')
                                            ->default(false),
                                    ]),
                            ]),

                        Tab::make('Password Policy')
                            ->icon(Heroicon::OutlinedLockClosed)
                            ->schema([
                                Section::make('Password Strength & Governance')
                                    ->description('Define password complexity rules and rotation requirements.')
                                    ->schema([
                                        TextInput::make('min_password_length')
                                            ->label('Minimum Password Length')
                                            ->numeric()
                                            ->minValue(6)
                                            ->maxValue(64)
                                            ->required(),
                                        TextInput::make('max_password_length')
                                            ->label('Maximum Password Length')
                                            ->numeric()
                                            ->minValue(12)
                                            ->maxValue(128)
                                            ->required(),
                                        Toggle::make('require_uppercase')
                                            ->label('Require Uppercase Letters (A-Z)')
                                            ->default(true),
                                        Toggle::make('require_lowercase')
                                            ->label('Require Lowercase Letters (a-z)')
                                            ->default(true),
                                        Toggle::make('require_number')
                                            ->label('Require Numerical Digits (0-9)')
                                            ->default(true),
                                        Toggle::make('require_special_characters')
                                            ->label('Require Special Characters (!@#$%^&*)')
                                            ->default(false),
                                        TextInput::make('password_history_limit')
                                            ->label('Password History Limit')
                                            ->helperText('Number of previous passwords users are restricted from reusing (0 = disabled).')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(20)
                                            ->default(3),
                                        TextInput::make('password_expiry_days')
                                            ->label('Password Expiry Period (Days)')
                                            ->helperText('Force password change after X days (0 = never expire).')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(365)
                                            ->default(90),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Security & Throttling')
                            ->icon(Heroicon::OutlinedShieldCheck)
                            ->schema([
                                Section::make('Authentication Security')
                                    ->description('Configure multi-factor policies, lockout limits, and brute-force protection.')
                                    ->schema([
                                        Toggle::make('force_2fa')
                                            ->label('Mandatory Multi-Factor Authentication')
                                            ->helperText('Enforce 2FA OTP codes across all users.')
                                            ->default(true),
                                        Toggle::make('allow_disabling_2fa')
                                            ->label('Allow Disabling 2FA in Profile Section')
                                            ->helperText('Allow or disallow individual users from turning off Email 2FA (MFA) from their personal profile.')
                                            ->default(false),
                                        TextInput::make('max_login_attempts')
                                            ->label('Max Consecutive Failed Login Attempts')
                                            ->numeric()
                                            ->minValue(3)
                                            ->maxValue(20)
                                            ->required(),
                                        TextInput::make('login_lockout_hours')
                                            ->label('Lockout Duration (Hours)')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(72)
                                            ->required(),
                                        TextInput::make('max_2fa_resend_attempts')
                                            ->label('Max 2FA Resend Limit')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(10)
                                            ->required(),
                                    ])
                                    ->columns(2),
                            ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->title('Settings updated successfully')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->submit('save'),
        ];
    }
}
