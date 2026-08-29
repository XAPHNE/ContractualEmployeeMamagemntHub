<?php

namespace App\Filament\Resources\Ddos\Pages;

use App\Filament\Resources\Ddos\DdoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDdos extends ManageRecords
{
    protected static string $resource = DdoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
