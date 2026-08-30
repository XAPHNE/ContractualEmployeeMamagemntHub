<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\Setting;
use App\Models\User;
use App\Rules\PasswordHistoryRule;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use SensitiveParameter;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static UnitEnum | string | null $navigationGroup = 'Management';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('User Details')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('General')
                            ->icon(Heroicon::OutlinedUser)
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Full Name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('Email Address')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->revealable()
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->rule(fn ($record) => Setting::getPasswordRules())
                                    ->rule(fn ($record) => new PasswordHistoryRule($record?->id))
                                    ->dehydrated(fn (#[SensitiveParameter] $state): bool => filled($state))
                                    ->dehydrateStateUsing(fn (#[SensitiveParameter] $state): string => Hash::make($state))
                                    ->same('passwordConfirmation'),
                                TextInput::make('passwordConfirmation')
                                    ->label('Confirm Password')
                                    ->password()
                                    ->revealable()
                                    ->requiredWith('password')
                                    ->dehydrated(false),
                            ]),
                        Tab::make('Security & Status')
                            ->icon(Heroicon::OutlinedShieldCheck)
                            ->columns(2)
                            ->schema([
                                \Filament\Forms\Components\Select::make('roles')
                                    ->label('Assigned Roles')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->columnSpanFull(),
                                Toggle::make('has_email_authentication')
                                    ->label('Two-Factor Authentication (Email MFA)')
                                    ->helperText('Requires 2FA OTP code on login.')
                                    ->default(false),
                                DateTimePicker::make('email_verified_at')
                                    ->label('Email Verified At')
                                    ->helperText('Leave empty for unverified accounts.'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                IconColumn::make('has_email_authentication')
                    ->label('Email MFA')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('email_verified_at')
                    ->label('Email Verified')
                    ->dateTime()
                    ->placeholder('Unverified')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    \Filament\Actions\ExportBulkAction::make()
                        ->exporter(\App\Filament\Exports\UserExporter::class),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
