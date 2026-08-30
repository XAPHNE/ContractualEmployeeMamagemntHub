<?php

namespace App\Filament\Resources\ApiKeys\Pages;

use App\Filament\Resources\ApiKeys\ApiKeyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageApiKeys extends ManageRecords
{
    protected static string $resource = ApiKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('xl'),
        ];
    }
}
