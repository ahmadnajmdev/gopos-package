<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Leaves\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Enums\LeaveStatus;

class LeavesTable
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
                TextColumn::make('leaveType.name')
                    ->label(__('Leave Type'))
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label(__('Start Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label(__('End Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('days')
                    ->label(__('Days'))
                    ->numeric(decimalPlaces: 1)
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('approver.name')
                    ->label(__('Approved By'))
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(LeaveStatus::class),
                SelectFilter::make('leave_type_id')
                    ->label(__('Leave Type'))
                    ->relationship('leaveType', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('employee_id')
                    ->label(__('Employee'))
                    ->relationship('employee', 'first_name')
                    ->searchable()
                    ->preload(),
                QueryBuilder::make()
                    ->constraints([
                        DateConstraint::make('start_date')
                            ->label(__('Start Date')),
                        DateConstraint::make('end_date')
                            ->label(__('End Date')),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                Action::make('approve')
                    ->label(__('Approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->authorize('update')
                    ->action(fn ($record) => $record->update([
                        'status' => LeaveStatus::Approved,
                        'approved_by' => auth()->id(),
                    ]))
                    ->visible(fn ($record): bool => $record->status === LeaveStatus::Pending),
                Action::make('reject')
                    ->label(__('Reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->authorize('update')
                    ->action(fn ($record) => $record->update([
                        'status' => LeaveStatus::Rejected,
                        'approved_by' => auth()->id(),
                    ]))
                    ->visible(fn ($record): bool => $record->status === LeaveStatus::Pending),
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
