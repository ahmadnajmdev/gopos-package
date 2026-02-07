@php
    $data = $this->getReportData();
    $totalAmount = 0;
    $totalDiscount = 0;
    $baseCurrencySymbol = \Gopos\Models\Currency::getBaseCurrency()?->symbol;
@endphp

<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="px-4 py-2 text-start">{{ __('Date') }}</th>
                <th class="px-4 py-2 text-start">{{ __('Invoice') }}</th>
                <th class="px-4 py-2 text-start">{{ __('Customer') }}</th>
                <th class="px-4 py-2 text-start">{{ __('Sub Total') }}</th>
                <th class="px-4 py-2 text-start">{{ __('Discount') }}</th>
                <th class="px-4 py-2 text-start">{{ __('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $sale)
                <tr class="border-b">
                    <td class="px-4 py-2">{{ $sale->sale_date }}</td>
                    <td class="px-4 py-2">{{ $sale->sale_number }}</td>
                    <td class="px-4 py-2">{{ $sale->customer->name ?? __('Walk-in Customer') }}</td>
                    @php $cur = $sale->currency?->symbol ?? $sale->currency?->code ?? $baseCurrencySymbol; @endphp
                    <td class="px-4 py-2 text-start">{{ number_format($sale->sub_total, 2) }} {{ $cur }}</td>
                    <td class="px-4 py-2 text-start">{{ number_format($sale->discount, 2) }} {{ $cur }}</td>
                    <td class="px-4 py-2 text-start">{{ number_format($sale->total_amount, 2) }} {{ $cur }}</td>
                </tr>
                @php
                    $totalAmount += $sale->total_amount;
                    $totalDiscount += $sale->discount;
                @endphp
            @endforeach
            <tr class="font-bold bg-gray-50 dark:bg-gray-700">
                <td colspan="4" class="px-4 py-2 text-start">{{ __('Total') }}</td>
                @php
                    $first = $data->first();
                    $totCur = $first?->currency?->symbol ?? $first?->currency?->code ?? $baseCurrencySymbol;
                @endphp
                <td class="px-4 py-2 text-start">{{ number_format($totalDiscount, 2) }} {{ $totCur }}</td>
                <td class="px-4 py-2 text-start">{{ number_format($totalAmount, 2) }} {{ $totCur }}</td>
            </tr>
        </tbody>
    </table>
</div>
