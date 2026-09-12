<?php

namespace App\Filament\Resources\EmployeeContributions\Pages;

use App\Filament\Resources\EmployeeContributions\EmployeeContributionResource;
use App\Models\Employee;
use App\Models\EmployeeContribution;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class ManageEmployeeContributions extends ManageRecords
{
    protected static string $resource = EmployeeContributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulk_add_contributions')
                ->label('Bulk Add Contributions')
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('primary')
                ->modalHeading('Bulk Contribution Entry')
                ->modalDescription('Record uniform monthly health insurance contribution deposited to GoA Treasury for multiple employees.')
                ->form([
                    Grid::make(2)->schema([
                        Select::make('fin_year')
                            ->label('Financial Year')
                            ->options(function () {
                                $currentYear = now()->month >= 4 ? now()->year : now()->year - 1;

                                return collect(range(-1, 2))->mapWithKeys(function ($offset) use ($currentYear) {
                                    $start = $currentYear + $offset;
                                    $fy = $start.'-'.substr((string) ($start + 1), -2);

                                    return [$fy => $fy];
                                })->all();
                            })
                            ->default(function () {
                                $currentYear = now()->month >= 4 ? now()->year : now()->year - 1;

                                return $currentYear.'-'.substr((string) ($currentYear + 1), -2);
                            })
                            ->live()
                            ->required(),

                        Select::make('month')
                            ->label('Contribution Month')
                            ->options([
                                1 => 'January (Month 1)',
                                2 => 'February (Month 2)',
                                3 => 'March (Month 3)',
                                4 => 'April (Month 4)',
                                5 => 'May (Month 5)',
                                6 => 'June (Month 6)',
                                7 => 'July (Month 7)',
                                8 => 'August (Month 8)',
                                9 => 'September (Month 9)',
                                10 => 'October (Month 10)',
                                11 => 'November (Month 11)',
                                12 => 'December (Month 12)',
                            ])
                            ->default(now()->month)
                            ->live()
                            ->required(),

                        TextInput::make('contribution_amount')
                            ->label('Contribution Amount (Per Employee)')
                            ->prefix('₹')
                            ->numeric()
                            ->default(225.0)
                            ->minValue(1)
                            ->required(),

                        DatePicker::make('contribution_date')
                            ->label('Contribution Date')
                            ->default(today())
                            ->maxDate(today())
                            ->required(),
                    ]),

                    CheckboxList::make('employee_ids')
                        ->label('Select Active Employees')
                        ->bulkToggleable()
                        ->searchable()
                        ->options(function (Get $get) {
                            $user = auth()->user();
                            $query = Employee::query()->active();

                            if ($user && ($ddo = $user->ddo)) {
                                $query->where('ddo_id', $ddo->id);
                            }

                            $selectedFinYear = $get('fin_year');
                            $selectedMonth = (int) ($get('month') ?: now()->month);

                            return $query->with(['contributions' => function ($q) use ($selectedFinYear) {
                                if ($selectedFinYear) {
                                    $q->where('fin_year', $selectedFinYear);
                                }
                            }])
                                ->orderBy('full_Name')
                                ->get()
                                ->filter(function ($emp) use ($selectedMonth) {
                                    // Rule: Exclude if already contributed for this or a later month in this fin_year
                                    $maxMonth = $emp->contributions->max('month');

                                    return $maxMonth === null || $selectedMonth > $maxMonth;
                                })
                                ->mapWithKeys(function ($emp) {
                                    return [$emp->id => "{$emp->full_Name} ({$emp->emp_id})"];
                                })
                                ->all();
                        })
                        ->helperText('Only active employees who have not already contributed for this or later months in the selected financial year are displayed.')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $finYear = $data['fin_year'];
                    $month = (int) $data['month'];
                    $amount = (float) $data['contribution_amount'];
                    $date = $data['contribution_date'];
                    $employeeIds = $data['employee_ids'] ?? [];

                    if (empty($employeeIds)) {
                        Notification::make()
                            ->title('No Employees Selected')
                            ->warning()
                            ->send();

                        return;
                    }

                    $successCount = 0;
                    $skippedCount = 0;
                    $reasons = [];

                    DB::transaction(function () use ($employeeIds, $finYear, $month, $amount, $date, &$successCount, &$skippedCount, &$reasons) {
                        $employees = Employee::whereIn('id', $employeeIds)->get();

                        foreach ($employees as $employee) {
                            // Rule 1: Active check
                            if (! $employee->isActive()) {
                                $skippedCount++;
                                $reasons[] = "{$employee->full_Name} is marked inactive.";

                                continue;
                            }

                            // Rule 2: Chronological check in the same fin_year
                            $latestMonth = $employee->contributions()
                                ->where('fin_year', $finYear)
                                ->max('month');

                            if ($latestMonth !== null && $month <= $latestMonth) {
                                $skippedCount++;
                                $reasons[] = "{$employee->full_Name} already has contribution up to Month {$latestMonth} in {$finYear}.";

                                continue;
                            }

                            EmployeeContribution::create([
                                'employee_id' => $employee->id,
                                'fin_year' => $finYear,
                                'month' => $month,
                                'contribution_amount' => $amount,
                                'contribution_date' => $date,
                            ]);

                            $successCount++;
                        }
                    });

                    if ($successCount > 0) {
                        Notification::make()
                            ->title('Bulk Contributions Recorded')
                            ->body("Successfully created {$successCount} contribution records for Month {$month} ({$finYear}).".($skippedCount > 0 ? " ({$skippedCount} skipped due to rules)" : ''))
                            ->success()
                            ->send();
                    }

                    if ($skippedCount > 0 && $successCount === 0) {
                        Notification::make()
                            ->title('No Contributions Created')
                            ->body("All selected employee(s) were skipped:\n".implode("\n", array_slice($reasons, 0, 5)))
                            ->warning()
                            ->send();
                    }
                }),
            CreateAction::make(),
        ];
    }
}
