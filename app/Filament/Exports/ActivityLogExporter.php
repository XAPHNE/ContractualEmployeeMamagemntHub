<?php

namespace App\Filament\Exports;

use App\Models\ActivityLog;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ActivityLogExporter extends Exporter
{
    protected static ?string $model = ActivityLog::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('Log ID'),
            ExportColumn::make('log_name')->label('Log Category'),
            ExportColumn::make('description')->label('Action / Description'),
            ExportColumn::make('subject_type')->label('Subject Type'),
            ExportColumn::make('subject_id')->label('Subject ID'),
            ExportColumn::make('causer.name')->label('Performed By'),
            ExportColumn::make('created_at')->label('Timestamp'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your activity logs export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
