<?php

namespace Gopos\Filament\Widgets\Sales;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Gopos\Models\SalePayment;
use Illuminate\Contracts\Support\Htmlable;

class RevenueByPaymentMethodWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = true;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '300px';

    public function getHeading(): string|Htmlable|null
    {
        return __('Revenue by Payment Method');
    }

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = SalePayment::query()
            ->selectRaw('payment_method, SUM(amount_in_base_currency) as total')
            ->groupBy('payment_method');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $payments = $query->get();

        $labels = [];
        $values = [];
        $colors = [
            'cash' => '#10B981',        // Green
            'card' => '#3B82F6',        // Blue
            'bank_transfer' => '#F59E0B', // Amber
            'mobile_payment' => '#8B5CF6', // Purple
            'credit' => '#EF4444',      // Red
        ];

        $backgroundColors = [];

        foreach ($payments as $payment) {
            $method = $payment->payment_method;
            $labels[] = __($this->formatPaymentMethod($method));
            $values[] = round($payment->total, 2);
            $backgroundColors[] = $colors[$method] ?? '#6B7280';
        }

        return [
            'datasets' => [
                [
                    'data' => $values,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }

    private function formatPaymentMethod(string $method): string
    {
        return match ($method) {
            'cash' => 'Cash',
            'card' => 'Card',
            'bank_transfer' => 'Bank Transfer',
            'mobile_payment' => 'Mobile Payment',
            'credit' => 'Credit',
            default => ucfirst(str_replace('_', ' ', $method)),
        };
    }
}
