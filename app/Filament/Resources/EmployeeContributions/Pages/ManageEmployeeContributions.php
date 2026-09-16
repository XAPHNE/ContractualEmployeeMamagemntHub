<?php

namespace App\Filament\Resources\EmployeeContributions\Pages;

use App\Filament\Resources\EmployeeContributions\EmployeeContributionResource;
use App\Models\Employee;
use App\Models\EmployeeContribution;
use App\Models\Setting;
use Carbon\Carbon;
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
use Illuminate\Database\Eloquent\Collection;
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
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $finYear = $get('fin_year');
                                    $month = (int) $value;

                                    if (! $finYear || ! $month) {
                                        return;
                                    }

                                    $user = auth()->user();
                                    $ddoId = $user?->ddo?->id;

                                    $missed = ManageEmployeeContributions::getMissedPreviousMonthEmployees($finYear, $month, $ddoId);

                                    if ($missed->isNotEmpty()) {
                                        $prev = ManageEmployeeContributions::getPreviousMonthAndFinYear($finYear, $month);
                                        $count = $missed->count();
                                        $names = $missed->take(3)->pluck('full_Name')->implode(', ');
                                        $more = $count > 3 ? ' and '.($count - 3).' more' : '';

                                        $fail("Cannot contribute for Month {$month}: {$count} active employee(s) ({$names}{$more}) have not had their Month {$prev['month']} ({$prev['fin_year']}) contributions deposited. Please clear Month {$prev['month']} backlog first.");
                                    }
                                },
                            ])
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
                        ->helperText(function () {
                            $allowSkip = filter_var(Setting::get('allow_skipping_contribution_months', false), FILTER_VALIDATE_BOOLEAN);

                            return $allowSkip
                                ? 'Only active employees who have not already contributed for this or later months in the selected financial year are displayed.'
                                : 'Sequential contribution enforced: DDO must ensure all active employees have previous month contributions recorded before progressing to the next month.';
                        })
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

                    $user = auth()->user();
                    $ddoId = $user?->ddo?->id;

                    $targetDdoIds = $ddoId
                        ? collect([$ddoId])
                        : Employee::whereIn('id', $employeeIds)->pluck('ddo_id')->unique()->filter();

                    $missedEmployees = collect();
                    foreach ($targetDdoIds as $dId) {
                        $missedEmployees = $missedEmployees->merge(
                            static::getMissedPreviousMonthEmployees($finYear, $month, (int) $dId)
                        );
                    }

                    if ($missedEmployees->isNotEmpty()) {
                        $prev = static::getPreviousMonthAndFinYear($finYear, $month);
                        $count = $missedEmployees->count();
                        $names = $missedEmployees->take(3)->pluck('full_Name')->implode(', ');
                        $more = $count > 3 ? ' and '.($count - 3).' more' : '';

                        Notification::make()
                            ->title('Previous Month Contribution Incomplete')
                            ->body("Cannot record Month {$month} contributions: {$count} active employee(s) ({$names}{$more}) have not had their Month {$prev['month']} ({$prev['fin_year']}) contributions deposited. Please clear all pending Month {$prev['month']} contributions first.")
                            ->danger()
                            ->persistent()
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

    /**
     * Resolve the previous month and financial year:
     * - Month 4 (April) checks Month 3 (March) of previous financial year.
     * - Month 1 (January) checks Month 12 (December) of the same financial year.
     * - Any other month checks (month - 1) of the same financial year.
     *
     * @return array{fin_year: string, month: int}
     */
    public static function getPreviousMonthAndFinYear(string $finYear, int $month): array
    {
        if ($month === 4) {
            $startYear = (int) explode('-', $finYear)[0];
            $prevStart = $startYear - 1;
            $prevFinYear = $prevStart.'-'.substr((string) ($prevStart + 1), -2);

            return [
                'fin_year' => $prevFinYear,
                'month' => 3,
            ];
        }

        if ($month === 1) {
            return [
                'fin_year' => $finYear,
                'month' => 12,
            ];
        }

        return [
            'fin_year' => $finYear,
            'month' => $month - 1,
        ];
    }

    /**
     * Get the calendar start date of the selected contribution month.
     * Months 4-12 are in the first year of the financial year.
     * Months 1-3 are in the second year of the financial year.
     */
    public static function getMonthStartDate(string $finYear, int $month): Carbon
    {
        $startYear = (int) explode('-', $finYear)[0];
        $calendarYear = ($month >= 4 && $month <= 12) ? $startYear : $startYear + 1;

        return Carbon::createFromDate($calendarYear, $month, 1)->startOfDay();
    }

    /**
     * Retrieve active employees of the DDO who missed contributing in the previous month.
     * Employees whose date_of_joining falls in or after the current month are excluded.
     * Returns an empty collection if skipping months is allowed in System Settings.
     */
    public static function getMissedPreviousMonthEmployees(string $finYear, int $month, ?int $ddoId = null): Collection
    {
        if (filter_var(Setting::get('allow_skipping_contribution_months', false), FILTER_VALIDATE_BOOLEAN)) {
            return new Collection;
        }

        $prev = static::getPreviousMonthAndFinYear($finYear, $month);
        $monthStart = static::getMonthStartDate($finYear, $month);

        return Employee::query()
            ->active()
            ->when($ddoId, fn ($q) => $q->where('ddo_id', $ddoId))
            ->where(function ($q) use ($monthStart) {
                $q->whereNull('date_of_joining')
                    ->orWhere('date_of_joining', '<', $monthStart);
            })
            ->whereDoesntHave('contributions', function ($q) use ($prev) {
                $q->where('fin_year', $prev['fin_year'])
                    ->where('month', $prev['month']);
            })
            ->orderBy('full_Name')
            ->get();
    }
}
