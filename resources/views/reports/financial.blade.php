<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Financial Report') }}</title>
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

        .profit {
            color: #059669;
        }

        .loss {
            color: #dc2626;
        }
    </style>
</head>
@php
    $locale = app()->getLocale();
    $rtlLocales = ['ar', 'ckb'];
    $isRtl = in_array($locale, $rtlLocales);
    $direction = $isRtl ? 'rtl' : 'ltr';
@endphp

<body dir="{{ $direction }}">

    <div class="header">
        <h1 class="text-center">{{ __('Financial Report') }}</h1>
        <p class="text-center">{{ __('Period') }}: {{ $startDate }} - {{ $endDate }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Category') }}</th>
                <th class="text-start">{{ __('Amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @php $base = \Gopos\Models\Currency::getBaseCurrency(); $baseLabel = $base?->symbol ?? $base?->code; @endphp
            <tr>
                <td>{{ __('Sales Revenue') }}</td>
                <td class="text-start">{{ number_format($data['sales'], 2) }} {{ $baseLabel }}</td>
            </tr>
            <tr>
                <td>{{ __('Other Income') }}</td>
                <td class="text-start">{{ number_format($data['incomes'], 2) }} {{ $baseLabel }}</td>
            </tr>
            <tr>
                <td>{{ __('Total Revenue') }}</td>
                <td class="text-start">{{ number_format($data['sales'] + $data['incomes'], 2) }} {{ $baseLabel }}
                </td>
            </tr>
            <tr>
                <td>{{ __('Purchases') }}</td>
                <td class="text-start">{{ number_format($data['purchases'], 2) }} {{ $baseLabel }}</td>
            </tr>
            <tr>
                <td>{{ __('Other Expenses') }}</td>
                <td class="text-start">{{ number_format($data['expenses'], 2) }} {{ $baseLabel }}</td>
            </tr>
            <tr>
                <td>{{ __('Total Expenses') }}</td>
                <td class="text-start">{{ number_format($data['purchases'] + $data['expenses'], 2) }}
                    {{ $baseLabel }}</td>
            </tr>
            <tr class="total-row">
                <td>{{ __('Net Profit/Loss') }}</td>
                <td class="text-start {{ $data['profit'] >= 0 ? 'profit' : 'loss' }}">
                    {{ number_format($data['profit'], 2) }} {{ $baseLabel }}
                </td>
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
