<?php

namespace App\Filament\Resources\AuthenticationLogs;

use App\Filament\Resources\AuthenticationLogs\Pages\ManageAuthenticationLogs;
use App\Models\AuthenticationLog;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AuthenticationLogResource extends Resource
{
    protected static ?string $model = AuthenticationLog::class;

    protected static ?string $navigationLabel = 'Authentication Logs';

    protected static UnitEnum | string | null $navigationGroup = 'Audit Hub';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ip_address')
                    ->label('IP Address')
                    ->disabled(),
                TextInput::make('user_agent')
                    ->label('User Agent')
                    ->disabled(),
                Toggle::make('login_successful')
                    ->label('Login Successful')
                    ->disabled(),
                DateTimePicker::make('login_at')
                    ->label('Login At')
                    ->disabled(),
                DateTimePicker::make('logout_at')
                    ->label('Logout At')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('login_at', 'desc')
            ->columns([
                TextColumn::make('authenticatable.name')
                    ->label('User')
                    ->description(fn (AuthenticationLog $record): ?string => $record->authenticatable?->email)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->copyable()
                    ->searchable(),
                IconColumn::make('login_successful')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('login_at')
                    ->label('Login Time')
                    ->dateTime('M d, Y h:i:s A')
                    ->sortable(),
                TextColumn::make('logout_at')
                    ->label('Logout Time')
                    ->dateTime('M d, Y h:i:s A')
                    ->placeholder('Active / Expired')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('user_agent')
                    ->label('User Agent')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->user_agent)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('successful_logins')
                    ->label('Successful Logins')
                    ->query(fn (Builder $query): Builder => $query->where('login_successful', true)),
                Filter::make('failed_logins')
                    ->label('Failed Attempts')
                    ->query(fn (Builder $query): Builder => $query->where('login_successful', false)),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAuthenticationLogs::route('/'),
        ];
    }
}
