<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\Currencies\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CurrenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('symbol')
                    ->label(__('Symbol'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('code')
                    ->label(__('Code'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->copyable()
                    ->copyMessage(__('Code copied')),
                TextColumn::make('exchange_rate')
                    ->label(__('Exchange Rate'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $record->base
                            ? __('Base Currency')
                            : number_format($state, 4)
                    )
                    ->color(fn ($record) => $record->base ? 'success' : 'gray'),
                TextColumn::make('decimal_places')
                    ->label(__('Decimal Places'))
                    ->numeric(locale: 'en')
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                IconColumn::make('base')
                    ->label(__('Base'))
                    ->boolean()
                    ->sortable()
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('base')
                    ->label(__('Base Currency'))
                    ->placeholder(__('All currencies'))
                    ->trueLabel(__('Base currencies only'))
                    ->falseLabel(__('Non-base currencies only')),
                SelectFilter::make('decimal_places')
                    ->label(__('Decimal Places'))
                    ->options([
                        0 => __('0 (No decimals)'),
                        1 => __('1 decimal place'),
                        2 => __('2 decimal places'),
                        3 => __('3 decimal places'),
                        4 => __('4 decimal places'),
                    ])
                    ->multiple(),
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('name')
                            ->label(__('Name')),
                        TextConstraint::make('code')
                            ->label(__('Code')),
                        TextConstraint::make('symbol')
                            ->label(__('Symbol')),
                        BooleanConstraint::make('base')
                            ->label(__('Base Currency')),
                        NumberConstraint::make('exchange_rate')
                            ->label(__('Exchange Rate')),
                        NumberConstraint::make('decimal_places')
                            ->label(__('Decimal Places'))
                            ->integer(),
                        DateConstraint::make('created_at')
                            ->label(__('Created at')),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make()
                    ->label(__('View')),
                EditAction::make()
                    ->label(__('Edit')),
                Action::make('toggle_base')
                    ->label(fn ($record) => $record->base ? __('Remove Base') : __('Set as Base'))
                    ->icon(fn ($record) => $record->base ? 'heroicon-o-x-mark' : 'heroicon-o-check')
                    ->color(fn ($record) => $record->base ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->base ? __('Remove Base') : __('Set as Base'))
                    ->modalDescription(fn ($record) => $record->base
                        ? __('Are you sure you want to remove the base status from this currency?')
                        : __('Are you sure you want to set this currency as base?').' '.__('This will remove the base status from other currencies.')
                    )
                    ->action(function ($record) {
                        if ($record->base) {
                            // Remove base status
                            $record->update(['base' => false]);
                        } else {
                            // Set as base and remove from others
                            \Gopos\Models\Currency::where('base', true)->update(['base' => false]);
                            $record->update(['base' => true, 'exchange_rate' => 1.0000]);
                        }
                    })
                    ->visible(fn ($record) => true),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('Delete selected records'))
                        ->requiresConfirmation()
                        ->modalHeading(__('Delete selected records'))
                        ->modalDescription(__('Are you sure you want to delete the selected records?'))
                        ->modalSubmitActionLabel(__('Delete')),
                ]),
            ])
            ->emptyStateHeading(__('No records found'))
            ->emptyStateDescription(__('No currencies have been created yet.'))
            ->emptyStateIcon('heroicon-o-currency-dollar')
            ->defaultSort('base', 'desc')
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }
}
