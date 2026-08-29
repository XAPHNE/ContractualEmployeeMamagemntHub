<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Ddos\DdoResource;
use App\Models\Ddo;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestDdosWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recently Added Employees / DDOs')
            ->query(
                Ddo::query()->latest()->limit(5)
            )
            ->columns([
                TextColumn::make('ddoId')
                    ->label('Employee ID')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                TextColumn::make('ddoName')
                    ->label('Employee Name')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('departmentName')
                    ->label('Department')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('postName')
                    ->label('Post Name'),
                TextColumn::make('districtName')
                    ->label('District'),
                TextColumn::make('created_at')
                    ->label('Registered At')
                    ->dateTime('M d, Y h:i A')
                    ->color('gray'),
            ])
            ->paginated(false);
    }
}
