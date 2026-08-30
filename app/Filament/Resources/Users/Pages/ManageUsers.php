<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Exports\UserExporter;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ManageRecords;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(UserExporter::class),
            CreateAction::make(),
        ];
    }
}
