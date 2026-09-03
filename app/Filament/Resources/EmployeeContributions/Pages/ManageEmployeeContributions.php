<?php

namespace App\Filament\Resources\EmployeeContributions\Pages;

use App\Filament\Resources\EmployeeContributions\EmployeeContributionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageEmployeeContributions extends ManageRecords
{
    protected static string $resource = EmployeeContributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
