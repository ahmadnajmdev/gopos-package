<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Currency;
use Gopos\Models\Customer;
use Illuminate\Support\Collection;

class CustomerBalancesReport extends BaseReport
{
    protected string $title = 'Customer Balances Report';

    protected bool $showTotals = true;

    protected array $columns = [
        'name' => ['label' => 'Customer Name', 'type' => 'text'],
        'phone' => ['label' => 'Phone', 'type' => 'text'],
        'email' => ['label' => 'Email', 'type' => 'text'],
        'total_sales' => ['label' => 'Total Sales', 'type' => 'currency'],
        'total_paid' => ['label' => 'Total Paid', 'type' => 'currency'],
        'balance_due' => ['label' => 'Balance Due', 'type' => 'currency'],
    ];

    protected array $totalColumns = ['total_sales', 'total_paid', 'balance_due'];

    public function getData(string $startDate, string $endDate): Collection
    {
        $baseCurrency = Currency::getBaseCurrency();
        $decimalPlaces = $baseCurrency?->decimal_places ?? 2;
        $currency = $baseCurrency?->symbol ?? $baseCurrency?->code;

        return Customer::query()
            ->with(['sales' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('sale_date', [$startDate, $endDate]);
            }, 'sales.currency'])
            ->get()
            ->map(function ($customer) use ($baseCurrency, $decimalPlaces, $currency) {
                $totalSales = $customer->sales->sum('amount_in_base_currency');

                // Calculate paid amount in base currency
                $totalPaid = $customer->sales->sum(function ($sale) use ($baseCurrency) {
                    if ($sale->currency_id == $baseCurrency?->id) {
                        return $sale->paid_amount;
                    }

                    return $sale->currency?->convertFromCurrency($sale->paid_amount, $sale->currency->code) ?? $sale->paid_amount;
                });

                $balanceDue = round($totalSales - $totalPaid, $decimalPlaces);

                return [
                    'name' => $customer->name,
                    'phone' => $customer->phone ?? 'N/A',
                    'email' => $customer->email ?? 'N/A',
                    'total_sales' => round($totalSales, $decimalPlaces),
                    'total_paid' => round($totalPaid, $decimalPlaces),
                    'balance_due' => $balanceDue,
                    'currency' => $currency,
                ];
            })
            ->sortByDesc('balance_due')
            ->values();
    }
}
