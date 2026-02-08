<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Payrolls\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Gopos\Enums\PayrollStatus;
use Gopos\Models\Employee;

class PayrollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Payroll Details'))
                    ->schema([
                        Select::make('employee_id')
                            ->label(__('Employee'))
                            ->relationship('employee', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->first_name} {$record->last_name}")
                            ->searchable(['first_name', 'last_name'])
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $employee = Employee::find($state);
                                    if ($employee) {
                                        $set('basic_salary', $employee->salary);
                                    }
                                }
                            }),
                        DatePicker::make('pay_period_start')
                            ->label(__('Pay Period Start'))
                            ->required()
                            ->default(now()->startOfMonth()),
                        DatePicker::make('pay_period_end')
                            ->label(__('Pay Period End'))
                            ->required()
                            ->default(now()->endOfMonth()),
                        Select::make('status')
                            ->label(__('Status'))
                            ->options(PayrollStatus::class)
                            ->default(PayrollStatus::Draft)
                            ->required()
                            ->live(),
                    ])
                    ->columns(2),

                Section::make(__('Salary Breakdown'))
                    ->schema([
                        TextInput::make('basic_salary')
                            ->label(__('Basic Salary'))
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                static::calculateNetPay($get, $set);
                            }),
                        TextInput::make('bonuses')
                            ->label(__('Bonuses'))
                            ->numeric()
                            ->step(0.01)
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                static::calculateNetPay($get, $set);
                            }),
                        TextInput::make('overtime_pay')
                            ->label(__('Overtime Pay'))
                            ->numeric()
                            ->step(0.01)
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                static::calculateNetPay($get, $set);
                            }),
                        TextInput::make('deductions')
                            ->label(__('Deductions'))
                            ->numeric()
                            ->step(0.01)
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                static::calculateNetPay($get, $set);
                            }),
                        TextInput::make('net_pay')
                            ->label(__('Net Pay'))
                            ->numeric()
                            ->step(0.01)
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(2),

                Section::make(__('Additional Information'))
                    ->schema([
                        DateTimePicker::make('paid_at')
                            ->label(__('Paid At'))
                            ->visible(fn (Get $get): bool => $get('status') === PayrollStatus::Paid->value || $get('status') === 'paid'),
                        Textarea::make('notes')
                            ->label(__('Notes'))
                            ->rows(3),
                    ])
                    ->collapsible(),
            ]);
    }

    protected static function calculateNetPay(Get $get, Set $set): void
    {
        $basicSalary = (float) ($get('basic_salary') ?? 0);
        $bonuses = (float) ($get('bonuses') ?? 0);
        $overtimePay = (float) ($get('overtime_pay') ?? 0);
        $deductions = (float) ($get('deductions') ?? 0);

        $set('net_pay', $basicSalary + $bonuses + $overtimePay - $deductions);
    }
}
