<?php

namespace App\Filament\Resources\AuthenticationLogs\Pages;

use App\Filament\Resources\AuthenticationLogs\AuthenticationLogResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAuthenticationLogs extends ManageRecords
{
    protected static string $resource = AuthenticationLogResource::class;
}
