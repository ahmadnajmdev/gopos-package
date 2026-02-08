<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Leaves\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Gopos\Enums\LeaveStatus;
use Illuminate\Support\Carbon;

class LeaveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Leave Details'))
                    ->schema([
                        Select::make('employee_id')
                            ->label(__('Employee'))
                            ->relationship('employee', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->first_name} {$record->last_name}")
                            ->searchable(['first_name', 'last_name'])
                            ->preload()
                            ->required(),
                        Select::make('leave_type_id')
                            ->label(__('Leave Type'))
                            ->relationship('leaveType', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(__('Name'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('days_allowed')
                                    ->label(__('Days Allowed'))
                                    ->numeric()
                                    ->required()
                                    ->minValue(1),
                                Toggle::make('is_paid')
                                    ->label(__('Paid Leave'))
                                    ->default(true),
                                Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->default(true),
                            ]),
                        DatePicker::make('start_date')
                            ->label(__('Start Date'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                static::calculateDays($get, $set);
                            }),
                        DatePicker::make('end_date')
                            ->label(__('End Date'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                static::calculateDays($get, $set);
                            }),
                        TextInput::make('days')
                            ->label(__('Days'))
                            ->numeric()
                            ->step(0.5)
                            ->required()
                            ->minValue(0.5),
                        Select::make('status')
                            ->label(__('Status'))
                            ->options(LeaveStatus::class)
                            ->default(LeaveStatus::Pending)
                            ->required(),
                        Textarea::make('reason')
                            ->label(__('Reason'))
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }

    protected static function calculateDays(Get $get, Set $set): void
    {
        $startDate = $get('start_date');
        $endDate = $get('end_date');

        if ($startDate && $endDate) {
            $days = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
            $set('days', $days);
        }
    }
}
