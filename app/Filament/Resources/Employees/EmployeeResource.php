<?php

namespace App\Filament\Resources\Employees;

use App\Filament\Resources\Employees\Pages\ManageEmployees;
use App\Models\Employee;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Management';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'Employee';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        $updateFullName = function (Get $get, Set $set): void {
            $parts = array_filter([$get('first_Name'), $get('middle_Name'), $get('last_Name')], fn ($part) => filled($part));
            $set('full_Name', implode(' ', $parts));
        };

        return $schema
            ->components([
                TextInput::make('emp_id')
                    ->required(),
                TextInput::make('full_Name')
                    ->label('Full Name')
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                TextInput::make('first_Name')
                    ->label('First Name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated($updateFullName),
                TextInput::make('middle_Name')
                    ->label('Middle Name')
                    ->nullable()
                    ->live(onBlur: true)
                    ->afterStateUpdated($updateFullName),
                TextInput::make('last_Name')
                    ->label('Last Name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated($updateFullName),
                Select::make('type')
                    ->options([
                        'Fixed Pay' => 'Fixed Pay',
                    ])
                    ->required(),
                TextInput::make('mobile')
                    ->mask('9999999999')
                    ->length(10)
                    ->rules(['digits:10'])
                    ->required(),
                Select::make('employee_code')
                    ->options([
                        'Fixed_Pay' => 'Fixed_Pay',
                    ])
                    ->required(),
                TextInput::make('pan')
                    ->maxLength(10)
                    ->required(),
                Select::make('gender')
                    ->options([
                        'Male' => 'Male',
                        'Female' => 'Female',
                        'Other' => 'Other',
                    ])
                    ->required(),
                DatePicker::make('dob')
                    ->required(),
                Select::make('designation')
                    ->options([
                        'Asst.' => 'Assistant',
                        'Asst.Teacher' => 'Assistant Teacher',
                        'Aya' => 'Aya',
                        'Chowkidar' => 'Chowkidar',
                        'Cleaner' => 'Cleaner',
                        'CLEANER(Engaged as Pump Operator)' => 'CLEANER (Engaged as Pump Operator)',
                        'Computer Typist' => 'Computer Typist',
                        'Cook' => 'Cook',
                        'Driver' => 'Driver',
                        'Ex. Serviceman' => 'Ex. Serviceman',
                        'Fix Pay Security' => 'Fix Pay Security',
                        'Gardener' => 'Gardener',
                        'Helper' => 'Helper',
                        'Helper to Mali' => 'Helper to Mali',
                        'LDA Cum Typist' => 'LDA Cum Typist',
                        'Mali' => 'Mali',
                        'OCFA' => 'Office Cum Field Assistant',
                        'Office Cleaner' => 'Office Cleaner',
                        'Office Peon' => 'Office Peon',
                        'Peon' => 'Peon',
                        'PTSC' => 'PTSC',
                        'Sahayak' => 'Sahayak',
                        'Sanitary Cleaner' => 'Sanitary Cleaner',
                        'Secuirty Guard' => 'Secuirty Guard',
                        'Teacher' => 'Teacher',
                        'Ward Attendant (Male)' => 'Ward Attendant (Male)',
                    ])
                    ->required(),
                Select::make('grade')
                    ->options([
                        'IV' => 'IV',
                    ])
                    ->required(),
                TextInput::make('pay_band')
                    ->nullable(),
                TextInput::make('grade_pay')
                    ->nullable(),
                DatePicker::make('date_of_joining')
                    ->required(),
                DatePicker::make('dor')
                    ->required(),
                TextInput::make('gpf_nps')
                    ->nullable(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->nullable(),
                TextInput::make('present_address')
                    ->required(),
                TextInput::make('permanent_address')
                    ->required(),
                TextInput::make('pincode')
                    ->required(),
                TextInput::make('district')
                    ->required(),
                Select::make('ddo_id')
                    ->label('Assigned DDO')
                    ->relationship('ddo', 'ddoName')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('active')
                    ->options([
                        'true' => 'Active',
                        'false' => 'Inactive',
                    ])
                    ->required(),
                TextInput::make('ac_number')
                    ->required(),
                Select::make('ac_type')
                    ->options([
                        'Savings' => 'Savings',
                        'Salary' => 'Salary',
                    ])
                    ->required(),
                TextInput::make('ac_name')
                    ->required(),
                Select::make('ac_bank')
                    ->options([
                        'SBI' => 'State Bank of India',
                        'BOB' => 'Bank of Baroda',
                        'BOI' => 'Bank of India',
                        'UCO' => 'UCO Bank',
                        'Canara' => 'Canara Bank',
                        'Bandhan' => 'Bandhan Bank',
                        'Allahabad' => 'Allahabad Bank',
                        'Corporation' => 'Corporation Bank',
                        'IDBI' => 'IDBI Bank',
                        'United Bank of India' => 'United Bank of India',
                        'Indian Bank' => 'Indian Bank',
                        'Indian Overseas Bank' => 'Indian Overseas Bank',
                        'Punjab National Bank' => 'Punjab National Bank',
                        'Union Bank of India' => 'Union Bank of India',
                        'ICICI Bank' => 'ICICI Bank',
                        'HDFC Bank' => 'HDFC Bank',
                        'Axis Bank' => 'Axis Bank',
                        'IndusInd Bank' => 'IndusInd Bank',
                        'Kotak Mahindra Bank' => 'Kotak Mahindra Bank',
                        'Yes Bank' => 'Yes Bank',
                        'IDFC First Bank' => 'IDFC First Bank',
                        'Bandhan Bank' => 'Bandhan Bank',
                        'RBL Bank' => 'RBL Bank',
                        'Federal Bank' => 'Federal Bank',
                    ])
                    ->required(),
                TextInput::make('ac_branch')
                    ->required(),
                TextInput::make('ac_ifsc')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Employee')
            ->columns([
                TextColumn::make('emp_id')
                    ->searchable(),
                TextColumn::make('full_Name')
                    ->searchable(),
                TextColumn::make('first_Name')
                    ->searchable(),
                TextColumn::make('middle_Name')
                    ->searchable(),
                TextColumn::make('last_Name')
                    ->searchable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('mobile')
                    ->searchable(),
                TextColumn::make('employee_code')
                    ->searchable(),
                TextColumn::make('pan')
                    ->searchable(),
                TextColumn::make('gender')
                    ->searchable(),
                TextColumn::make('dob')
                    ->searchable(),
                TextColumn::make('designation')
                    ->searchable(),
                TextColumn::make('grade')
                    ->searchable(),
                TextColumn::make('pay_band')
                    ->searchable(),
                TextColumn::make('grade_pay')
                    ->searchable(),
                TextColumn::make('date_of_joining')
                    ->date()
                    ->sortable(),
                TextColumn::make('dor')
                    ->date()
                    ->sortable(),
                TextColumn::make('gpf_nps')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('present_address')
                    ->searchable(),
                TextColumn::make('permanent_address')
                    ->searchable(),
                TextColumn::make('pincode')
                    ->searchable(),
                TextColumn::make('district')
                    ->searchable(),
                TextColumn::make('ddo.ddoName')
                    ->label('Assigned DDO')
                    ->placeholder('None')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('active')
                    ->searchable(),
                TextColumn::make('ac_number')
                    ->searchable(),
                TextColumn::make('ac_type')
                    ->searchable(),
                TextColumn::make('ac_name')
                    ->searchable(),
                TextColumn::make('ac_bank')
                    ->searchable(),
                TextColumn::make('ac_branch')
                    ->searchable(),
                TextColumn::make('ac_ifsc')
                    ->searchable(),
                TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updatedBy.name')
                    ->label('Updated By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deletedBy.name')
                    ->label('Deleted By')
                    ->placeholder('N/A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageEmployees::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
