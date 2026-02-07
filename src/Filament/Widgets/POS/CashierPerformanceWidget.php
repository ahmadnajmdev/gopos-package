<?php

namespace Gopos\Filament\Widgets\POS;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Gopos\Models\Currency;
use Gopos\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CashierPerformanceWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = true;

    protected static ?int $sort = 21;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $symbol = Currency::getBaseCurrency()?->symbol ?? '';

        return $table
            ->query(function () use ($startDate, $endDate): Builder {
                return User::query()
                    ->whereHas('posSessions', function ($q) use ($startDate, $endDate) {
                        if ($startDate && $endDate) {
                            $q->whereBetween('opening_time', [$startDate, $endDate]);
                        }
                    })
                    ->withCount(['posSessions' => function ($q) use ($startDate, $endDate) {
                        if ($startDate && $endDate) {
                            $q->whereBetween('opening_time', [$startDate, $endDate]);
                        }
                    }])
                    ->withSum(['posSessions' => function ($q) use ($startDate, $endDate) {
                        if ($startDate && $endDate) {
                            $q->whereBetween('opening_time', [$startDate, $endDate]);
                        }
                    }], 'cash_difference')
                    ->orderByDesc('pos_sessions_count')
                    ->limit(10);
            })
            ->heading(__('Cashier Performance'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('Cashier'))
                    ->searchable(),

                TextColumn::make('pos_sessions_count')
                    ->label(__('Sessions'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('total_sales_amount')
                    ->label(__('Total Sales'))
                    ->numeric(locale: 'en')
                    ->suffix(' '.$symbol)
                    ->getStateUsing(function ($record) use ($startDate, $endDate) {
                        $query = $record->posSessions();
                        if ($startDate && $endDate) {
                            $query->whereBetween('opening_time', [$startDate, $endDate]);
                        }

                        return $query->get()->sum(function ($session) {
                            return $session->sales()->sum('amount_in_base_currency');
                        });
                    }),

                TextColumn::make('avg_per_session')
                    ->label(__('Avg/Session'))
                    ->numeric(locale: 'en')
                    ->suffix(' '.$symbol)
                    ->getStateUsing(function ($record) use ($startDate, $endDate) {
                        $query = $record->posSessions();
                        if ($startDate && $endDate) {
                            $query->whereBetween('opening_time', [$startDate, $endDate]);
                        }
                        $sessions = $query->get();
                        $totalSales = $sessions->sum(function ($session) {
                            return $session->sales()->sum('amount_in_base_currency');
                        });
                        $sessionCount = $sessions->count() ?: 1;

                        return round($totalSales / $sessionCount, 2);
                    }),

                TextColumn::make('pos_sessions_sum_cash_difference')
                    ->label(__('Variance'))
                    ->numeric(locale: 'en')
                    ->suffix(' '.$symbol)
                    ->color(fn ($state) => $state == 0 ? 'success' : ($state > 0 ? 'warning' : 'danger')),
            ])
            ->paginated(false)
            ->emptyStateHeading(__('No cashier data'))
            ->emptyStateDescription(__('No POS sessions found for the selected period'));
    }
}
