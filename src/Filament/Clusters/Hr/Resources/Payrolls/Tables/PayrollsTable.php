<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Payrolls\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Enums\PayrollStatus;

class PayrollsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.first_name')
                    ->label(__('Employee'))
                    ->formatStateUsing(fn ($record): string => "{$record->employee->first_name} {$record->employee->last_name}")
                    ->searchable(['employee.first_name', 'employee.last_name'])
                    ->sortable(),
                TextColumn::make('pay_period_start')
                    ->label(__('Period Start'))
                    ->date()
                    ->sortable(),
                TextColumn::make('pay_period_end')
                    ->label(__('Period End'))
                    ->date()
                    ->sortable(),
                TextColumn::make('basic_salary')
                    ->label(__('Basic Salary'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('deductions')
                    ->label(__('Deductions'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('bonuses')
                    ->label(__('Bonuses'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('overtime_pay')
                    ->label(__('Overtime'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('net_pay')
                    ->label(__('Net Pay'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label(__('Paid At'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(PayrollStatus::class),
                SelectFilter::make('employee_id')
                    ->label(__('Employee'))
                    ->relationship('employee', 'first_name')
                    ->searchable()
                    ->preload(),
                QueryBuilder::make()
                    ->constraints([
                        DateConstraint::make('pay_period_start')
                            ->label(__('Period Start')),
                        DateConstraint::make('pay_period_end')
                            ->label(__('Period End')),
                        NumberConstraint::make('net_pay')
                            ->label(__('Net Pay')),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                Action::make('mark_processed')
                    ->label(__('Mark Processed'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->authorize('update')
                    ->action(fn ($record) => $record->update(['status' => PayrollStatus::Processed]))
                    ->visible(fn ($record): bool => $record->status === PayrollStatus::Draft),
                Action::make('mark_paid')
                    ->label(__('Mark Paid'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->authorize('update')
                    ->action(fn ($record) => $record->update([
                        'status' => PayrollStatus::Paid,
                        'paid_at' => now(),
                    ]))
                    ->visible(fn ($record): bool => $record->status === PayrollStatus::Processed),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
