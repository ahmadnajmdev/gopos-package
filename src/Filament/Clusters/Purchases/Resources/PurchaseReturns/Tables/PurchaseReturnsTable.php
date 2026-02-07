<?php

namespace Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseReturnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchase_return_number')
                    ->label(__('Return Number'))
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                TextColumn::make('purchase.purchase_number')
                    ->label(__('Purchase Number'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('purchase.supplier.name')
                    ->label(__('Supplier'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('purchase_return_date')
                    ->label(__('Return Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('currency.code')
                    ->label(__('Currency'))
                    ->sortable(),
                TextColumn::make('sub_total')
                    ->label(__('Subtotal'))
                    ->numeric()
                    ->sortable()
                    ->money(fn ($record) => $record->currency?->code),
                TextColumn::make('discount')
                    ->numeric()
                    ->sortable()
                    ->money(fn ($record) => $record->currency?->code),
                TextColumn::make('total_amount')
                    ->label(__('Total'))
                    ->numeric()
                    ->sortable()
                    ->money(fn ($record) => $record->currency?->code)
                    ->weight('bold'),
                TextColumn::make('paid_amount')
                    ->label(__('Paid'))
                    ->numeric()
                    ->sortable()
                    ->money(fn ($record) => $record->currency?->code),
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'Pending',
                        'success' => 'Completed',
                        'danger' => 'Rejected',
                    ]),
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
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'Pending' => __('Pending'),
                        'Completed' => __('Completed'),
                        'Rejected' => __('Rejected'),
                    ]),
                SelectFilter::make('currency_id')
                    ->label(__('Currency'))
                    ->relationship('currency', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('today')
                    ->label(__('Today'))
                    ->query(fn (Builder $query): Builder => $query->whereDate('purchase_return_date', today()))
                    ->toggle(),
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('purchase_return_number')
                            ->label(__('Return Number')),
                        RelationshipConstraint::make('purchase')
                            ->label(__('Purchase'))
                            ->relationship('purchase', 'purchase_number'),
                        RelationshipConstraint::make('currency')
                            ->label(__('Currency'))
                            ->relationship('currency', 'name'),
                        NumberConstraint::make('sub_total')
                            ->label(__('Subtotal')),
                        NumberConstraint::make('discount')
                            ->label(__('Discount')),
                        NumberConstraint::make('total_amount')
                            ->label(__('Total Amount')),
                        NumberConstraint::make('paid_amount')
                            ->label(__('Paid Amount')),
                        SelectConstraint::make('status')
                            ->label(__('Status'))
                            ->options([
                                'Pending' => __('Pending'),
                                'Completed' => __('Completed'),
                                'Rejected' => __('Rejected'),
                            ]),
                        DateConstraint::make('purchase_return_date')
                            ->label(__('Return Date')),
                        DateConstraint::make('created_at')
                            ->label(__('Created at')),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
