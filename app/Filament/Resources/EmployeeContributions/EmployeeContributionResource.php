<?php

namespace App\Filament\Resources\EmployeeContributions;

use App\Filament\Resources\EmployeeContributions\Pages\ManageEmployeeContributions;
use App\Models\EmployeeContribution;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeContributionResource extends Resource
{
    protected static ?string $model = EmployeeContribution::class;

    protected static ?string $navigationLabel = 'Contributions';

    protected static \UnitEnum|string|null $navigationGroup = 'Management';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('Employee')
                    ->relationship('employee', 'full_Name')
                    ->searchable(['emp_id','full_Name'])
                    ->preload()
                    ->required(),
                Select::make('month')
                    ->label('Month')
                    ->options([
                        1 => 'January',
                        2 => 'February',
                        3 => 'March',
                        4 => 'April',
                        5 => 'May',
                        6 => 'June',
                        7 => 'July',
                        8 => 'August',
                        9 => 'September',
                        10 => 'October',
                        11 => 'November',
                        12 => 'December',
                    ])
                    ->required(),
                TextInput::make('fin_year')
                    ->label('Financial Year')
                    ->placeholder('e.g. 2026-27')
                    ->regex('/^\d{4}-\d{2}$/')
                    ->datalist(function () {
                        $currentYear = now()->month >= 4 ? now()->year : now()->year - 1;
                        return collect(range(-1, 1))->mapWithKeys(function ($offset) use ($currentYear) {
                            $start = $currentYear + $offset;
                            $fy = $start . '-' . substr((string) ($start + 1), -2);
                            return [$fy => $fy];
                        })->values()->all();
                    })
                    ->required(),
                TextInput::make('contribution_amount')
                    ->label('Contribution Amount')
                    ->prefix('₹')
                    ->numeric()
                    ->default(225.0)
                    ->minValue(0)
                    ->maxValue(9999)
                    ->required(),
                DatePicker::make('contribution_date')
                    ->label('Contribution Date')
                    ->readOnly()
                    ->default(today())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.full_Name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.emp_id')
                    ->label('Employee ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fin_year')
                    ->label('Financial Year')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('month')
                    ->label('Month')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('contribution_amount')
                    ->label('Amount')
                    ->money('INR')
                    ->sortable(),
                TextColumn::make('contribution_date')
                    ->label('Contribution Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageEmployeeContributions::route('/'),
        ];
    }
}
