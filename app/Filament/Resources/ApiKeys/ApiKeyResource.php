<?php

namespace App\Filament\Resources\ApiKeys;

use App\Filament\Resources\ApiKeys\Pages\ManageApiKeys;
use App\Models\ApiKey;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ApiKeyResource extends Resource
{
    protected static ?string $model = ApiKey::class;

    protected static ?string $navigationLabel = 'API Credentials';

    protected static UnitEnum | string | null $navigationGroup = 'API Manager';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('is_active', true)->count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Client & Authentication')
                    ->columnSpanFull()
                    ->description('Provision and govern secure integration tokens for external services (e.g. SAP ERP).')
                    ->schema([
                        TextInput::make('name')
                            ->label('Client / Application Name')
                            ->placeholder('e.g. SAP ERP Payment Processing')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('key')
                            ->label('API Secret Key')
                            ->default(fn () => ApiKey::generateKey())
                            ->required()
                            ->readOnly()
                            ->copyable()
                            ->helperText('Provide this token to the SAP ERP team for authentication via X-API-KEY header or Bearer Token.')
                            ->columnSpanFull(),
                        Textarea::make('allowed_ips')
                            ->label('Whitelisted IP Addresses')
                            ->placeholder('e.g. 192.168.1.100, 10.0.0.45')
                            ->helperText('Comma-separated list of SAP server IP addresses. Leave empty to allow any IP address.')
                            ->columnSpanFull(),
                        TextInput::make('rate_limit_per_minute')
                            ->label('Rate Limit (Requests / Min)')
                            ->numeric()
                            ->default(60)
                            ->minValue(1)
                            ->maxValue(5000)
                            ->required(),
                        DateTimePicker::make('expires_at')
                            ->label('Token Expiration Date')
                            ->helperText('Optional expiry timestamp. Leave empty for permanent tokens.'),
                        Toggle::make('is_active')
                            ->label('Token Active Status')
                            ->default(true)
                            ->helperText('Instantly disable all API access without deleting this token.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Client Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('key')
                    ->label('API Key')
                    ->copyable()
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => substr($state, 0, 14) . '••••••••••••••••' . substr($state, -4)),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('rate_limit_per_minute')
                    ->label('Rate Limit')
                    ->suffix(' req/min')
                    ->sortable(),
                TextColumn::make('last_used_at')
                    ->label('Last Used')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('Never Used')
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('Expires At')
                    ->dateTime('M d, Y')
                    ->placeholder('Never')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('regenerateKey')
                    ->label('Regenerate')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Regenerate API Secret Key')
                    ->modalDescription('Are you sure you want to regenerate this API key? External systems using the old token will immediately lose access until updated.')
                    ->action(function (ApiKey $record) {
                        $record->update(['key' => ApiKey::generateKey()]);
                        Notification::make()
                            ->title('API Key regenerated successfully')
                            ->success()
                            ->send();
                    }),
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
            'index' => ManageApiKeys::route('/'),
        ];
    }
}
