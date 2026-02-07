<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\Employees;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\HumanResources\HumanResourcesCluster;
use Gopos\Filament\Clusters\HumanResources\Resources\Employees\Pages\CreateEmployee;
use Gopos\Filament\Clusters\HumanResources\Resources\Employees\Pages\EditEmployee;
use Gopos\Filament\Clusters\HumanResources\Resources\Employees\Pages\ListEmployees;
use Gopos\Filament\Clusters\HumanResources\Resources\Employees\Pages\ViewEmployee;
use Gopos\Models\Currency;
use Gopos\Models\Department;
use Gopos\Models\Employee;
use Gopos\Models\Position;
use Gopos\Models\WorkSchedule;

class EmployeeResource extends Resource
{
    protected static ?string $cluster = HumanResourcesCluster::class;

    protected static ?string $model = Employee::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Human Resources');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make(__('Basic Information'))
                            ->schema([
                                Section::make(__('Personal Information'))
                                    ->schema([
                                        FileUpload::make('photo')
                                            ->label(__('Photo'))
                                            ->image()
                                            ->avatar()
                                            ->directory('employees')
                                            ->columnSpanFull()
                                            ->maxSize(2048),
                                        TextInput::make('employee_number')
                                            ->label(__('Employee Number'))
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->placeholder(__('Auto-generated')),
                                        Select::make('user_id')
                                            ->label(__('System User'))
                                            ->relationship('user', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->nullable(),
                                        TextInput::make('first_name')
                                            ->label(__('First Name'))
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('last_name')
                                            ->label(__('Last Name'))
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('first_name_ar')
                                            ->label(__('First Name (Arabic)'))
                                            ->maxLength(255),
                                        TextInput::make('last_name_ar')
                                            ->label(__('Last Name (Arabic)'))
                                            ->maxLength(255),
                                        TextInput::make('first_name_ckb')
                                            ->label(__('First Name (Kurdish)'))
                                            ->maxLength(255),
                                        TextInput::make('last_name_ckb')
                                            ->label(__('Last Name (Kurdish)'))
                                            ->maxLength(255),
                                        Select::make('gender')
                                            ->label(__('Gender'))
                                            ->options([
                                                'male' => __('Male'),
                                                'female' => __('Female'),
                                            ])
                                            ->required(),
                                        DatePicker::make('birth_date')
                                            ->label(__('Birth Date'))
                                            ->minDate(now()->subYears(100))
                                            ->maxDate(now()->subYears(16)),
                                        Select::make('marital_status')
                                            ->label(__('Marital Status'))
                                            ->options([
                                                'single' => __('Single'),
                                                'married' => __('Married'),
                                                'divorced' => __('Divorced'),
                                                'widowed' => __('Widowed'),
                                            ]),
                                        TextInput::make('nationality')
                                            ->label(__('Nationality'))
                                            ->maxLength(100),
                                        TextInput::make('national_id')
                                            ->label(__('National ID'))
                                            ->maxLength(50),
                                        TextInput::make('passport_number')
                                            ->label(__('Passport Number'))
                                            ->maxLength(50),
                                    ])->columns(2),
                            ]),
                        Tab::make(__('Contact Information'))
                            ->schema([
                                Section::make(__('Contact Details'))
                                    ->schema([
                                        TextInput::make('email')
                                            ->label(__('Email'))
                                            ->email()
                                            ->maxLength(255),
                                        TextInput::make('phone')
                                            ->label(__('Phone'))
                                            ->tel()
                                            ->maxLength(20),
                                        TextInput::make('mobile')
                                            ->label(__('Mobile'))
                                            ->tel()
                                            ->maxLength(20),
                                        Textarea::make('address')
                                            ->label(__('Address'))
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        TextInput::make('city')
                                            ->label(__('City'))
                                            ->maxLength(100),
                                        TextInput::make('state')
                                            ->label(__('State/Province'))
                                            ->maxLength(100),
                                        TextInput::make('country')
                                            ->label(__('Country'))
                                            ->maxLength(100),
                                    ])->columns(2),
                                Section::make(__('Emergency Contact'))
                                    ->schema([
                                        TextInput::make('emergency_contact_name')
                                            ->label(__('Contact Name'))
                                            ->maxLength(255),
                                        TextInput::make('emergency_contact_phone')
                                            ->label(__('Contact Phone'))
                                            ->tel()
                                            ->maxLength(20),
                                        TextInput::make('emergency_contact_relation')
                                            ->label(__('Relationship'))
                                            ->maxLength(100),
                                    ])->columns(3),
                            ]),
                        Tab::make(__('Employment'))
                            ->schema([
                                Section::make(__('Employment Details'))
                                    ->schema([
                                        Select::make('department_id')
                                            ->label(__('Department'))
                                            ->options(Department::active()->pluck('name', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live(),
                                        Select::make('position_id')
                                            ->label(__('Position'))
                                            ->options(fn ($get) => Position::active()
                                                ->when($get('department_id'), fn ($q, $deptId) => $q->where('department_id', $deptId))
                                                ->pluck('title', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Select::make('manager_id')
                                            ->label(__('Direct Manager'))
                                            ->options(fn ($record) => Employee::active()
                                                ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                                ->get()
                                                ->pluck('display_name', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->nullable(),
                                        Select::make('work_schedule_id')
                                            ->label(__('Work Schedule'))
                                            ->options(WorkSchedule::where('is_active', true)->pluck('name', 'id'))
                                            ->searchable()
                                            ->preload(),
                                        Select::make('employment_type')
                                            ->label(__('Employment Type'))
                                            ->options([
                                                'full_time' => __('Full Time'),
                                                'part_time' => __('Part Time'),
                                                'contract' => __('Contract'),
                                                'temporary' => __('Temporary'),
                                                'intern' => __('Intern'),
                                            ])
                                            ->required(),
                                        Select::make('status')
                                            ->label(__('Status'))
                                            ->options([
                                                'active' => __('Active'),
                                                'on_leave' => __('On Leave'),
                                                'suspended' => __('Suspended'),
                                                'terminated' => __('Terminated'),
                                                'resigned' => __('Resigned'),
                                            ])
                                            ->default('active')
                                            ->required(),
                                        DatePicker::make('hire_date')
                                            ->label(__('Hire Date'))
                                            ->required()
                                            ->default(now()),
                                        DatePicker::make('probation_end_date')
                                            ->label(__('Probation End Date'))
                                            ->afterOrEqual('hire_date'),
                                        DatePicker::make('contract_end_date')
                                            ->label(__('Contract End Date'))
                                            ->afterOrEqual('hire_date'),
                                    ])->columns(2),
                            ]),
                        Tab::make(__('Salary & Bank'))
                            ->schema([
                                Section::make(__('Salary Information'))
                                    ->schema([
                                        TextInput::make('basic_salary')
                                            ->label(__('Basic Salary'))
                                            ->numeric()
                                            ->minValue(0)
                                            ->prefix('$')
                                            ->required(),
                                        Select::make('salary_type')
                                            ->label(__('Salary Type'))
                                            ->options([
                                                'monthly' => __('Monthly'),
                                                'hourly' => __('Hourly'),
                                                'daily' => __('Daily'),
                                            ])
                                            ->default('monthly'),
                                        Select::make('currency_id')
                                            ->label(__('Currency'))
                                            ->options(Currency::pluck('name', 'id'))
                                            ->searchable()
                                            ->preload(),
                                    ])->columns(3),
                                Section::make(__('Bank Details'))
                                    ->schema([
                                        TextInput::make('bank_name')
                                            ->label(__('Bank Name'))
                                            ->maxLength(255),
                                        TextInput::make('bank_account_number')
                                            ->label(__('Account Number'))
                                            ->maxLength(50),
                                        TextInput::make('bank_iban')
                                            ->label(__('IBAN'))
                                            ->maxLength(50),
                                    ])->columns(3),
                            ]),
                        Tab::make(__('Notes'))
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Textarea::make('notes')
                                            ->label(__('Notes'))
                                            ->rows(5)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->circular()
                    ->label(__('Photo')),
                TextColumn::make('employee_number')
                    ->label(__('Employee #'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label(__('Name'))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name']),
                TextColumn::make('department.name')
                    ->label(__('Department'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('position.title')
                    ->label(__('Position'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('employment_type')
                    ->label(__('Type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'full_time' => __('Full Time'),
                        'part_time' => __('Part Time'),
                        'contract' => __('Contract'),
                        'temporary' => __('Temporary'),
                        'intern' => __('Intern'),
                        default => $state,
                    })
                    ->colors([
                        'success' => 'full_time',
                        'info' => 'part_time',
                        'warning' => 'contract',
                        'gray' => 'temporary',
                        'primary' => 'intern',
                    ])
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => __('Active'),
                        'on_leave' => __('On Leave'),
                        'suspended' => __('Suspended'),
                        'terminated' => __('Terminated'),
                        'resigned' => __('Resigned'),
                        default => $state,
                    })
                    ->colors([
                        'success' => 'active',
                        'warning' => 'on_leave',
                        'danger' => ['suspended', 'terminated', 'resigned'],
                    ]),
                TextColumn::make('hire_date')
                    ->label(__('Hire Date'))
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('mobile')
                    ->label(__('Mobile'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->label(__('Department'))
                    ->options(Department::active()->pluck('name', 'id')),
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'active' => __('Active'),
                        'on_leave' => __('On Leave'),
                        'suspended' => __('Suspended'),
                        'terminated' => __('Terminated'),
                        'resigned' => __('Resigned'),
                    ]),
                SelectFilter::make('employment_type')
                    ->label(__('Employment Type'))
                    ->options([
                        'full_time' => __('Full Time'),
                        'part_time' => __('Part Time'),
                        'contract' => __('Contract'),
                        'temporary' => __('Temporary'),
                        'intern' => __('Intern'),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('employee_number', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Personal Information'))
                    ->schema([
                        ImageEntry::make('photo')
                            ->label(__('Photo'))
                            ->circular()
                            ->columnSpanFull(),
                        TextEntry::make('employee_number')
                            ->label(__('Employee Number')),
                        TextEntry::make('full_name')
                            ->label(__('Name')),
                        TextEntry::make('gender')
                            ->label(__('Gender'))
                            ->formatStateUsing(fn ($state) => $state === 'male' ? __('Male') : __('Female')),
                        TextEntry::make('birth_date')
                            ->label(__('Birth Date'))
                            ->date(),
                        TextEntry::make('nationality')
                            ->label(__('Nationality')),
                        TextEntry::make('national_id')
                            ->label(__('National ID')),
                    ])->columns(2),
                Section::make(__('Employment'))
                    ->schema([
                        TextEntry::make('department.name')
                            ->label(__('Department')),
                        TextEntry::make('position.title')
                            ->label(__('Position')),
                        TextEntry::make('manager.full_name')
                            ->label(__('Manager')),
                        TextEntry::make('employment_type')
                            ->label(__('Employment Type'))
                            ->badge(),
                        TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'on_leave' => 'warning',
                                default => 'danger',
                            }),
                        TextEntry::make('hire_date')
                            ->label(__('Hire Date'))
                            ->date(),
                        TextEntry::make('basic_salary')
                            ->label(__('Basic Salary'))
                            ->money('USD'),
                    ])->columns(2),
                Section::make(__('Contact'))
                    ->schema([
                        TextEntry::make('email')
                            ->label(__('Email')),
                        TextEntry::make('phone')
                            ->label(__('Phone')),
                        TextEntry::make('mobile')
                            ->label(__('Mobile')),
                        TextEntry::make('address')
                            ->label(__('Address')),
                    ])->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getLabel(): string
    {
        return __('Employee');
    }

    public static function getPluralLabel(): string
    {
        return __('Employees');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'view' => ViewEmployee::route('/{record}'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }
}
