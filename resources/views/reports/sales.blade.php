<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Sales Report') }}</title>
    <style>
        @page {
            margin: 2cm;
        }

        body {
            font-family: 'Rabar', sans-serif;
            line-height: 1.5;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        .table th,
        .table td {
            padding: 0.75rem;
            border: 1px solid #dee2e6;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .text-start {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .header {
            margin-bottom: 2rem;
        }
        .header h1 {
            margin: 0 0 0.5rem 0;
            color: #1a56db;
        }
        .header p {
            margin: 0;
            color: #6b7280;
        }
    </style>
</head>
@php
    $locale = app()->getLocale();
    $rtlLocales = ['ar','ckb'];
    $isRtl = in_array($locale, $rtlLocales);
    $direction = $isRtl ? 'rtl' : 'ltr';
    $baseCurrencySymbol = \Gopos\Models\Currency::getBaseCurrency()?->symbol;
@endphp
<body dir="{{ $direction }}">
    <div class="header">
        <h1 class="text-center">{{ __('Sales Report') }}</h1>
        <p class="text-center">{{ __('Period') }}: {{ $startDate }} - {{ $endDate }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Invoice') }}</th>
                <th>{{ __('Customer') }}</th>
                <th class="text-start">{{ __('Sub Total') }}</th>
                <th class="text-start">{{ __('Discount') }}</th>
                <th class="text-start">{{ __('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalAmount = 0;
                $totalDiscount = 0;
            @endphp
            @foreach($data as $sale)
                <tr>
                    <td>{{ $sale->sale_date }}</td>
                    <td>{{ $sale->sale_number }}</td>
                    <td>{{ $sale->customer->name ?? __('Walk-in Customer') }}</td>
                    @php $cur = $sale->currency?->symbol ?? $sale->currency?->code ?? $baseCurrencySymbol; @endphp
                    <td class="text-start">{{ number_format($sale->sub_total, 2) }} {{ $cur }}</td>
                    <td class="text-start">{{ number_format($sale->discount_amount, 2) }} {{ $cur }}</td>
                    <td class="text-start">{{ number_format($sale->total_amount, 2) }} {{ $cur }}</td>
                </tr>
                @php
                    $totalAmount += $sale->total_amount;
                    $totalDiscount += $sale->discount_amount;
                @endphp
            @endforeach
            <tr class="total-row">
                <td colspan="4" class="text-start">{{ __('Total') }}</td>
                @php
                    $first = $data->first();
                    $totCur = $first?->currency?->symbol ?? $first?->currency?->code ?? $baseCurrencySymbol;
                @endphp
                <td class="text-start">{{ number_format($totalDiscount, 2) }} {{ $totCur }}</td>
                <td class="text-start">{{ number_format($totalAmount, 2) }} {{ $totCur }}</td>
            </tr>
        </tbody>
    </table>

    <footer>
        <p class="text-center" style="color: #6b7280; font-size: 0.875rem; margin-top: 2rem;">
            {{ __('Generated on') }}: {{ now()->format('Y-m-d H:i:s') }}
        </p>
    </footer>
</body>
</html>
