<?php

namespace Gopos\Filament\Widgets\POS;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Gopos\Models\Currency;
use Gopos\Models\PosSession;
use Gopos\Models\Sale;
use Illuminate\Support\HtmlString;

class POSSessionSummaryWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 20;

    protected function getStats(): array
    {
        $baseCurrency = Currency::getBaseCurrency();
        $symbol = $baseCurrency?->symbol ?? $baseCurrency?->code ?? 'IQD';
        $decimals = $baseCurrency?->decimal_places ?? 0;

        // Active sessions
        $activeSessions = PosSession::where('status', 'open')->count();

        // Today's sessions
        $todaySessions = PosSession::whereDate('opening_time', today())->count();

        // Today's POS revenue
        $todayPosRevenue = Sale::whereDate('sale_date', today())
            ->whereNotNull('pos_session_id')
            ->sum('amount_in_base_currency');

        $todayPosTransactions = Sale::whereDate('sale_date', today())
            ->whereNotNull('pos_session_id')
            ->count();

        // Cash variance (sum of cash_difference from closed sessions today)
        $cashVariance = PosSession::whereDate('opening_time', today())
            ->where('status', 'closed')
            ->sum('cash_difference');

        // Average session duration (in hours)
        $avgDuration = PosSession::whereDate('opening_time', today())
            ->where('status', 'closed')
            ->whereNotNull('closing_time')
            ->get()
            ->avg(function ($session) {
                return $session->opening_time->diffInMinutes($session->closing_time);
            });
        $avgDurationFormatted = $avgDuration ? round($avgDuration / 60, 1).' '.__('hrs') : '-';

        $varianceColor = $cashVariance == 0 ? 'success' : ($cashVariance > 0 ? 'warning' : 'danger');

        return [
            Stat::make(__('Active Sessions'), number_format($activeSessions))
                ->description(__('Currently open'))
                ->icon('heroicon-o-computer-desktop')
                ->value(new HtmlString('<span class="text-success-600">'.number_format($activeSessions).'</span>'))
                ->color('success'),

            Stat::make(__("Today's Sessions"), number_format($todaySessions))
                ->description(__('Opened today'))
                ->icon('heroicon-o-calendar')
                ->color('primary'),

            Stat::make(__('POS Revenue'), number_format($todayPosRevenue, $decimals).' '.$symbol)
                ->description(__(':count transactions', ['count' => $todayPosTransactions]))
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make(__('Cash Variance'), number_format(abs($cashVariance), $decimals).' '.$symbol)
                ->description($cashVariance >= 0 ? __('Over') : __('Short'))
                ->icon('heroicon-o-scale')
                ->value(new HtmlString('<span class="text-'.$varianceColor.'-600">'.($cashVariance >= 0 ? '+' : '-').number_format(abs($cashVariance), $decimals).' '.$symbol.'</span>'))
                ->color($varianceColor),

            Stat::make(__('Avg. Duration'), $avgDurationFormatted)
                ->description(__('Session length'))
                ->icon('heroicon-o-clock')
                ->color('info'),
        ];
    }
}
