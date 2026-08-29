<?php

namespace App\Filament\Resources\Ddos;

use App\Filament\Resources\Ddos\Pages\ManageDdos;
use App\Models\Ddo;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DdoResource extends Resource
{
    protected static ?string $model = Ddo::class;

    protected static ?string $navigationLabel = 'Employees / DDOs';

    protected static \UnitEnum | string | null $navigationGroup = 'Management';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $recordTitleAttribute = 'ddoName';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ddoId')
                    ->label('Employee ID')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->mask('99999999')
                    ->length(8)
                    ->rules(['digits:8'])
                    ->validationMessages([
                        'digits' => 'The Employee ID must be exactly 8 digits.',
                        'min' => 'The Employee ID must be at least 8 digits.',
                    ]),
                TextInput::make('ddoName')
                    ->label('Employee Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('pan')
                    ->label('PAN')
                    ->required()
                    ->maxLength(10),
                TextInput::make('departmentName')
                    ->label('Department Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('directorate')
                    ->label('Directorate')
                    ->maxLength(255),
                TextInput::make('postName')
                    ->label('Post Name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('officeName')
                    ->label('Office Name')
                    ->required()
                    ->rows(3)
                    ->maxLength(255),
                Textarea::make('officeAddress')
                    ->label('Office Address')
                    ->required()
                    ->rows(3)
                    ->maxLength(255),
                TextInput::make('mobileNumber')
                    ->label('Mobile Number')
                    ->required()
                    ->tel()
                    ->maxLength(10),
                TextInput::make('treasuryName')
                    ->label('Treasury Name')
                    ->nullable()
                    ->maxLength(255),
                TextInput::make('treasuryCode')
                    ->label('Treasury Code')
                    ->nullable()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->email()
                    ->maxLength(255),
                TextInput::make('districtName')
                    ->label('District Name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ddoId')
                    ->label('Employee ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ddoName')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pan')
                    ->label('PAN')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('departmentName')
                    ->label('Department')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('directorate')
                    ->label('Directorate')
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('postName')
                    ->label('Post')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('officeName')
                    ->label('Office')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('officeAddress')
                    ->label('Office Address')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('mobileNumber')
                    ->label('Mobile')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('treasuryName')
                    ->label('Treasury')
                    ->placeholder('N/A')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('treasuryCode')
                    ->label('Treasury Code')
                    ->placeholder('N/A')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('districtName')
                    ->label('District')
                    ->searchable()
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
                TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => ManageDdos::route('/'),
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
