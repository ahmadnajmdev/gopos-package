<?php

namespace Gopos\Filament\Widgets\Inventory;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Gopos\Models\ProductBatch;
use Illuminate\Database\Eloquent\Builder;

class ExpiringProductsWidget extends TableWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ProductBatch::query()
                ->with(['product', 'warehouse'])
                ->where('quantity', '>', 0)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays(30))
                ->orderBy('expiry_date')
                ->limit(10)
            )
            ->heading(__('Expiring Products (Within 30 Days)'))
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('Product'))
                    ->searchable(),

                TextColumn::make('batch_number')
                    ->label(__('Batch'))
                    ->searchable(),

                TextColumn::make('warehouse.name')
                    ->label(__('Warehouse')),

                TextColumn::make('quantity')
                    ->label(__('Qty'))
                    ->numeric(),

                TextColumn::make('expiry_date')
                    ->label(__('Expiry Date'))
                    ->date()
                    ->sortable(),

                TextColumn::make('days_until_expiry')
                    ->label(__('Days Left'))
                    ->getStateUsing(function ($record) {
                        $days = now()->diffInDays($record->expiry_date, false);

                        return $days;
                    })
                    ->badge()
                    ->color(function ($state) {
                        if ($state < 0) {
                            return 'danger';
                        }
                        if ($state <= 7) {
                            return 'danger';
                        }
                        if ($state <= 14) {
                            return 'warning';
                        }

                        return 'success';
                    })
                    ->formatStateUsing(function ($state) {
                        if ($state < 0) {
                            return __('Expired');
                        }

                        return $state.' '.__('days');
                    }),
            ])
            ->paginated(false)
            ->emptyStateHeading(__('No expiring products'))
            ->emptyStateDescription(__('No products expiring within the next 30 days'));
    }
}
