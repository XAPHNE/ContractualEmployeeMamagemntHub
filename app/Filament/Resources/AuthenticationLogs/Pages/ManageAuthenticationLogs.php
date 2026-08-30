<?php

namespace App\Filament\Resources\AuthenticationLogs\Pages;

use App\Filament\Exports\AuthenticationLogExporter;
use App\Filament\Resources\AuthenticationLogs\AuthenticationLogResource;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAuthenticationLogs extends ManageRecords
{
    protected static string $resource = AuthenticationLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(AuthenticationLogExporter::class),
        ];
    }
}
