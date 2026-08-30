<?php

namespace App\Filament\Exports;

use App\Models\Ddo;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class DdoExporter extends Exporter
{
    protected static ?string $model = Ddo::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('ddoId')->label('Employee ID'),
            ExportColumn::make('ddoName')->label('Employee Name'),
            ExportColumn::make('pan')->label('PAN'),
            ExportColumn::make('departmentName')->label('Department Name'),
            ExportColumn::make('directorate')->label('Directorate'),
            ExportColumn::make('postName')->label('Post Name'),
            ExportColumn::make('officeName')->label('Office Name'),
            ExportColumn::make('officeAddress')->label('Office Address'),
            ExportColumn::make('mobileNumber')->label('Mobile Number'),
            ExportColumn::make('treasuryName')->label('Treasury Name'),
            ExportColumn::make('treasuryCode')->label('Treasury Code'),
            ExportColumn::make('email')->label('Email'),
            ExportColumn::make('districtName')->label('District Name'),
            ExportColumn::make('created_at')->label('Created At'),
            ExportColumn::make('createdBy.name')->label('Created By'),
            ExportColumn::make('updated_at')->label('Updated At'),
            ExportColumn::make('updatedBy.name')->label('Updated By'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your employee/DDO records export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
