<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Employees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Gopos\Enums\EmployeeStatus;
use Gopos\Enums\Gender;
use Gopos\Models\Position;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Personal Information'))
                    ->schema([
                        TextInput::make('employee_number')
                            ->label(__('Employee Number'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('EMP-0001'),
                        TextInput::make('first_name')
                            ->label(__('First Name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label(__('Last Name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('Phone'))
                            ->tel()
                            ->maxLength(255),
                        DatePicker::make('date_of_birth')
                            ->label(__('Date of Birth'))
                            ->maxDate(now()->subYears(16)),
                        Select::make('gender')
                            ->label(__('Gender'))
                            ->options(Gender::class),
                    ])
                    ->columns(2),

                Section::make(__('Employment Details'))
                    ->schema([
                        DatePicker::make('hire_date')
                            ->label(__('Hire Date'))
                            ->required()
                            ->default(now()),
                        Select::make('department_id')
                            ->label(__('Department'))
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->nullable()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(__('Name'))
                                    ->required()
                                    ->maxLength(255),
                                Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->default(true),
                            ]),
                        Select::make('position_id')
                            ->label(__('Position'))
                            ->options(function (Get $get) {
                                $departmentId = $get('department_id');

                                if ($departmentId) {
                                    return Position::query()
                                        ->where('department_id', $departmentId)
                                        ->where('is_active', true)
                                        ->pluck('title', 'id');
                                }

                                return Position::query()
                                    ->where('is_active', true)
                                    ->pluck('title', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->createOptionForm([
                                TextInput::make('title')
                                    ->label(__('Title'))
                                    ->required()
                                    ->maxLength(255),
                                Select::make('department_id')
                                    ->label(__('Department'))
                                    ->relationship('department', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->default(true),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return Position::create($data)->getKey();
                            }),
                        TextInput::make('salary')
                            ->label(__('Salary'))
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0),
                        Select::make('status')
                            ->label(__('Status'))
                            ->options(EmployeeStatus::class)
                            ->default(EmployeeStatus::Active)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make(__('Additional Information'))
                    ->schema([
                        Textarea::make('address')
                            ->label(__('Address'))
                            ->rows(3),
                        Textarea::make('notes')
                            ->label(__('Notes'))
                            ->rows(3),
                    ])
                    ->collapsible(),
            ]);
    }
}
