@php
    $data = $this->getReportData();
@endphp

@php
    $locale = app()->getLocale();
    $rtlLocales = ['ar','ckb'];
    $isRtl = in_array($locale, $rtlLocales);
    $direction = $isRtl ? 'rtl' : 'ltr';
@endphp
<div class="overflow-x-auto" dir="{{ $direction }}">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="px-4 py-2 text-start">{{ __('Category') }}</th>
                <th class="px-4 py-2 text-start">{{ __('Amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @php $base = \Gopos\Models\Currency::getBaseCurrency(); $baseLabel = $base?->symbol ?? $base?->code; @endphp
            <tr class="border-b">
                <td class="px-4 py-2">{{ __('Sales Revenue') }}</td>
                <td class="px-4 py-2 text-start">{{ number_format($data['sales'], 2) }} {{ $baseLabel }}</td>
            </tr>
            <tr class="border-b">
                <td class="px-4 py-2">{{ __('Other Income') }}</td>
                <td class="px-4 py-2 text-start">{{ number_format($data['incomes'], 2) }} {{ $baseLabel }}</td>
            </tr>
            <tr class="border-b">
                <td class="px-4 py-2">{{ __('Purchases') }}</td>
                <td class="px-4 py-2 text-start">{{ number_format($data['purchases'], 2) }} {{ $baseLabel }}</td>
            </tr>
            <tr class="border-b">
                <td class="px-4 py-2">{{ __('Expenses') }}</td>
                <td class="px-4 py-2 text-start">{{ number_format($data['expenses'], 2) }} {{ $baseLabel }}</td>
            </tr>
            <tr class="font-bold bg-gray-50 dark:bg-gray-700">
                <td class="px-4 py-2">{{ __('Net Profit/Loss') }}</td>
                <td class="px-4 py-2 text-start {{ $data['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ number_format($data['profit'], 2) }} {{ $baseLabel }}
                </td>
            </tr>
        </tbody>
    </table>
</div>
