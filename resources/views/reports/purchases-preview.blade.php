@php
    $data = $this->getReportData();
    $totalAmount = 0;
    $totalDiscount = 0;
    $totalPaid = 0;
    $baseCurrencySymbol = \Gopos\Models\Currency::getBaseCurrency()?->symbol;
@endphp

<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="px-4 py-2 text-start">{{ __('Date') }}</th>
                <th class="px-4 py-2 text-start">{{ __('Purchase Number') }}</th>
                <th class="px-4 py-2 text-start">{{ __('Supplier') }}</th>
                <th class="px-4 py-2 text-start">{{ __('Sub Total') }}</th>
                <th class="px-4 py-2 text-start">{{ __('Discount') }}</th>
                <th class="px-4 py-2 text-start">{{ __('Total') }}</th>
                <th class="px-4 py-2 text-start">{{ __('Paid amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $purchase)
                <tr class="border-b">
                    <td class="px-4 py-2">{{ $purchase->purchase_date }}</td>
                    <td class="px-4 py-2">{{ $purchase->purchase_number }}</td>
                    <td class="px-4 py-2">{{ $purchase->supplier->name }}</td>
                    @php $cur = $purchase->currency?->symbol ?? $purchase->currency?->code ?? $baseCurrencySymbol; @endphp
                    <td class="px-4 py-2 text-start">{{ number_format($purchase->sub_total, 2) }} {{ $cur }}</td>
                    <td class="px-4 py-2 text-start">{{ number_format($purchase->discount_amount, 2) }} {{ $cur }}</td>
                    <td class="px-4 py-2 text-start">{{ number_format($purchase->total_amount, 2) }} {{ $cur }}</td>
                    <td class="px-4 py-2 text-start">{{ number_format($purchase->paid_amount, 2) }} {{ $cur }}</td>
                </tr>
                @php
                    $totalAmount += $purchase->total_amount;
                    $totalDiscount += $purchase->discount_amount;
                    $totalPaid += $purchase->paid_amount;
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
                <td class="px-4 py-2 text-start">{{ number_format($totalPaid, 2) }} {{ $totCur }}</td>
            </tr>
        </tbody>
    </table>
</div>
