<?php

namespace Gopos\Filament\Widgets\Sales;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Gopos\Models\Currency;
use Gopos\Models\Customer;
use Illuminate\Database\Eloquent\Builder;

class TopCustomersWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = true;

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $symbol = Currency::getBaseCurrency()?->symbol ?? '';

        return $table
            ->query(function () use ($startDate, $endDate): Builder {
                $query = Customer::query()
                    ->withSum(['sales' => function ($q) use ($startDate, $endDate) {
                        if ($startDate && $endDate) {
                            $q->whereBetween('sale_date', [$startDate, $endDate]);
                        }
                    }], 'amount_in_base_currency')
                    ->withCount(['sales' => function ($q) use ($startDate, $endDate) {
                        if ($startDate && $endDate) {
                            $q->whereBetween('sale_date', [$startDate, $endDate]);
                        }
                    }])
                    ->having('sales_sum_amount_in_base_currency', '>', 0)
                    ->orderByDesc('sales_sum_amount_in_base_currency')
                    ->limit(10);

                return $query;
            })
            ->heading(__('Top Customers'))
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=C&color=7F9CF5&background=EBF4FF'),

                TextColumn::make('name')
                    ->label(__('Customer'))
                    ->searchable(),

                TextColumn::make('sales_sum_amount_in_base_currency')
                    ->label(__('Total Purchases'))
                    ->numeric(locale: 'en')
                    ->suffix(' '.$symbol)
                    ->sortable(),

                TextColumn::make('sales_count')
                    ->label(__('Orders'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('avg_order')
                    ->label(__('Avg. Order'))
                    ->numeric(locale: 'en')
                    ->suffix(' '.$symbol)
                    ->getStateUsing(function ($record) {
                        if ($record->sales_count > 0) {
                            return round($record->sales_sum_amount_in_base_currency / $record->sales_count, 2);
                        }

                        return 0;
                    }),

                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated(false);
    }
}
