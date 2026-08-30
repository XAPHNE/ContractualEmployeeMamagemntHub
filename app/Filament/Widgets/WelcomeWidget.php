<?php

namespace App\Filament\Widgets;

use Filament\Widgets\AccountWidget;

class WelcomeWidget extends AccountWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -3;
}
