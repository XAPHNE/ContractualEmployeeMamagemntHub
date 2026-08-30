<?php

namespace App\Filament\Resources\ApiLogs;

use App\Filament\Resources\ApiLogs\Pages\ManageApiLogs;
use App\Models\ApiLog;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ApiLogResource extends Resource
{
    protected static ?string $model = ApiLog::class;

    protected static ?string $navigationLabel = 'API Access Logs';

    protected static UnitEnum | string | null $navigationGroup = 'Audit Hub';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedServer;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('client_name')
                    ->label('Client Application')
                    ->disabled()
                    ->columnSpan(1),
                TextInput::make('ip_address')
                    ->label('Caller IP')
                    ->disabled()
                    ->columnSpan(1),
                TextInput::make('endpoint')
                    ->label('Endpoint Path')
                    ->disabled()
                    ->columnSpan(1),
                TextInput::make('method')
                    ->label('HTTP Method')
                    ->disabled()
                    ->columnSpan(1),
                TextInput::make('status_code')
                    ->label('Response Status Code')
                    ->disabled()
                    ->columnSpan(1),
                TextInput::make('records_count')
                    ->label('Dispatched Records Count')
                    ->disabled()
                    ->columnSpan(1),
                TextInput::make('duration_ms')
                    ->label('Latency (ms)')
                    ->disabled()
                    ->columnSpanFull(),
                KeyValue::make('request_params')
                    ->label('Query / Payload Parameters')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('client_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('method')
                    ->label('Method')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('endpoint')
                    ->label('Endpoint')
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('status_code')
                    ->label('Status')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 200 && $state < 300 => 'success',
                        $state >= 400 && $state < 500 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('records_count')
                    ->label('Records')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('duration_ms')
                    ->label('Latency')
                    ->suffix(' ms')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('M d, Y h:i:s A')
                    ->sortable(),
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
            'index' => ManageApiLogs::route('/'),
        ];
    }
}
