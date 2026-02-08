<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Employees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Enums\EmployeeStatus;
use Gopos\Enums\Gender;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_number')
                    ->label(__('Employee Number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label(__('Full Name'))
                    ->state(fn ($record): string => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name']),
                TextColumn::make('department.name')
                    ->label(__('Department'))
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('position.title')
                    ->label(__('Position'))
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('salary')
                    ->label(__('Salary'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('hire_date')
                    ->label(__('Hire Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label(__('Gender'))
                    ->badge()
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
                    ->options(EmployeeStatus::class),
                SelectFilter::make('department_id')
                    ->label(__('Department'))
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('gender')
                    ->label(__('Gender'))
                    ->options(Gender::class),
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('employee_number')
                            ->label(__('Employee Number')),
                        TextConstraint::make('first_name')
                            ->label(__('First Name')),
                        TextConstraint::make('last_name')
                            ->label(__('Last Name')),
                        NumberConstraint::make('salary')
                            ->label(__('Salary')),
                        DateConstraint::make('hire_date')
                            ->label(__('Hire Date')),
                        DateConstraint::make('created_at')
                            ->label(__('Created at')),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('employee_number');
    }
}
