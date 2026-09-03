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
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('month')
                    ->label('Month (1-12)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(12)
                    ->required(),
                TextInput::make('fin_year')
                    ->label('Financial Year')
                    ->placeholder('e.g. 2024-25')
                    ->required(),
                TextInput::make('contribution_amount')
                    ->label('Contribution Amount')
                    ->prefix('₹')
                    ->numeric()
                    ->required(),
                DatePicker::make('contribution_date')
                    ->label('Contribution Date')
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
