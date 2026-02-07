<?php

namespace Gopos\Filament\Widgets\Purchases;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Gopos\Models\Currency;
use Gopos\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;

class TopSuppliersWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = true;

    protected static ?int $sort = 15;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $symbol = Currency::getBaseCurrency()?->symbol ?? '';

        return $table
            ->query(function () use ($startDate, $endDate): Builder {
                return Supplier::query()
                    ->withSum(['purchases' => function ($q) use ($startDate, $endDate) {
                        if ($startDate && $endDate) {
                            $q->whereBetween('purchase_date', [$startDate, $endDate]);
                        }
                    }], 'amount_in_base_currency')
                    ->withCount(['purchases' => function ($q) use ($startDate, $endDate) {
                        if ($startDate && $endDate) {
                            $q->whereBetween('purchase_date', [$startDate, $endDate]);
                        }
                    }])
                    ->having('purchases_sum_amount_in_base_currency', '>', 0)
                    ->orderByDesc('purchases_sum_amount_in_base_currency')
                    ->limit(10);
            })
            ->heading(__('Top Suppliers'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('Supplier'))
                    ->searchable(),

                TextColumn::make('purchases_sum_amount_in_base_currency')
                    ->label(__('Total Purchases'))
                    ->numeric(locale: 'en')
                    ->suffix(' '.$symbol)
                    ->sortable(),

                TextColumn::make('purchases_count')
                    ->label(__('Orders'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('outstanding')
                    ->label(__('Outstanding'))
                    ->numeric(locale: 'en')
                    ->suffix(' '.$symbol)
                    ->getStateUsing(function ($record) {
                        return $record->purchases()
                            ->selectRaw('SUM(amount_in_base_currency - paid_amount) as outstanding')
                            ->value('outstanding') ?? 0;
                    })
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),

                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated(false);
    }
}
