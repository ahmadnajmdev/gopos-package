<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Currency;
use Gopos\Models\Customer;
use Illuminate\Support\Collection;

class TopCustomersReport extends BaseReport
{
    protected string $title = 'Top Customers Report';

    protected bool $showTotals = true;

    protected array $columns = [
        'rank' => ['label' => 'Rank', 'type' => 'text'],
        'name' => ['label' => 'Customer Name', 'type' => 'text'],
        'phone' => ['label' => 'Phone', 'type' => 'text'],
        'email' => ['label' => 'Email', 'type' => 'text'],
        'total_orders' => ['label' => 'Total Orders', 'type' => 'number', 'suffix' => 'orders'],
        'total_amount' => ['label' => 'Total Amount', 'type' => 'currency'],
    ];

    protected array $totalColumns = ['total_orders', 'total_amount'];

    public function getData(string $startDate, string $endDate, ?int $branchId = null, bool $allBranches = false): Collection
    {
        $baseCurrency = Currency::getBaseCurrency();
        $decimalPlaces = $baseCurrency?->decimal_places ?? 2;
        $currency = $baseCurrency?->symbol ?? $baseCurrency?->code;

        $query = Customer::query();

        if ($allBranches) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName());
        } elseif ($branchId) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName())
                ->where('branch_id', $branchId);
        }

        return $query
            ->with(['sales' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('sale_date', [$startDate, $endDate]);
            }])
            ->get()
            ->map(function ($customer) use ($decimalPlaces, $currency) {
                $totalOrders = $customer->sales->count();
                $totalAmount = $customer->sales->sum('amount_in_base_currency');

                return [
                    'name' => $customer->name,
                    'phone' => $customer->phone ?? 'N/A',
                    'email' => $customer->email ?? 'N/A',
                    'total_orders' => $totalOrders,
                    'total_amount' => round($totalAmount, $decimalPlaces),
                    'currency' => $currency,
                ];
            })
            ->filter(fn ($item) => $item['total_orders'] > 0)
            ->sortByDesc('total_amount')
            ->take(20)
            ->values()
            ->map(function ($item, $index) {
                return array_merge(['rank' => '#'.($index + 1)], $item);
            });
    }
}
