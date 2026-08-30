<?php

namespace App\Filament\Exports;

use App\Models\AuthenticationLog;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AuthenticationLogExporter extends Exporter
{
    protected static ?string $model = AuthenticationLog::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('Log ID'),
            ExportColumn::make('authenticatable.name')->label('User Name'),
            ExportColumn::make('authenticatable.email')->label('User Email'),
            ExportColumn::make('ip_address')->label('IP Address'),
            ExportColumn::make('user_agent')->label('User Agent'),
            ExportColumn::make('login_successful')->label('Successful'),
            ExportColumn::make('login_at')->label('Login Timestamp'),
            ExportColumn::make('logout_at')->label('Logout Timestamp'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your authentication logs export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
