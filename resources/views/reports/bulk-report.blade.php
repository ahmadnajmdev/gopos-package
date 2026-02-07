<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Combined Reports') }}</title>
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
        .subtotal-row {
            font-weight: bold;
            background-color: #f1f5f9;
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
        .report-section {
            margin-bottom: 2rem;
        }
        .report-title {
            margin-top: 1rem;
            margin-bottom: 1rem;
            color: #1a56db;
            font-size: 1.25rem;
            font-weight: bold;
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
    $rtlLocales = ['ar','ckb'];
    $isRtl = in_array($locale, $rtlLocales);
    $direction = $isRtl ? 'rtl' : 'ltr';
@endphp
<body dir="{{ $direction }}">
    <div class="header">
        <h1 class="text-center">{{ __('Combined Reports') }}</h1>
        <p class="text-center">{{ __('Period') }}: {{ $startDate }} - {{ $endDate }}</p>
        <p class="text-center" style="color: #6b7280; font-size: 0.875rem;">
            {{ __('Generated on') }}: {{ now()->format('Y-m-d H:i:s') }}
        </p>
    </div>

    @foreach($reports as $index => $reportData)
        @if($index > 0)
            <pagebreak>
        @endif

        <div class="report-section">
            <h2 class="report-title">{{ __($reportData['report']->getTitle()) }}</h2>

            <table class="table">
                <thead>
                    <tr>
                        @foreach($reportData['report']->getColumns() as $key => $column)
                            <th class="{{ in_array($column['type'], ['currency', 'number']) ? 'text-start' : '' }}">
                                {{ __($column['label']) }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @if(is_array($reportData['data']) && isset($reportData['data']['rows']))
                        {{-- Financial Report Style --}}
                        @foreach($reportData['data']['rows'] as $row)
                            <tr class="{{ $row['is_total'] ?? false ? 'total-row' : ($row['is_subtotal'] ?? false ? 'subtotal-row' : '') }}">
                                @foreach($reportData['report']->getColumns() as $key => $column)
                                    <td class="{{ in_array($column['type'], ['currency', 'number']) ? 'text-start' : '' }} {{ $key === 'amount' && isset($row['amount']) && $row['amount'] < 0 ? 'loss' : '' }} {{ $key === 'amount' && isset($row['amount']) && $row['amount'] >= 0 && ($row['is_total'] ?? false) ? 'profit' : '' }}">
                                        @if($column['type'] === 'currency')
                                            {{ number_format($row[$key], 2) }} {{ $row['currency'] ?? '' }}
                                        @elseif($column['type'] === 'number')
                                            {{ number_format($row[$key], 2) }}
                                        @else
                                            {{ $row[$key] }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @else
                        {{-- Standard Table Report Style --}}
                        @php
                            $totals = [];
                            foreach($reportData['report']->getTotalColumns() as $col) {
                                $totals[$col] = 0;
                            }
                        @endphp

                        @foreach($reportData['data'] as $row)
                            <tr>
                                @foreach($reportData['report']->getColumns() as $key => $column)
                                    <td class="{{ in_array($column['type'], ['currency', 'number']) ? 'text-start' : '' }}">
                                        @if($column['type'] === 'currency')
                                            {{ number_format($row[$key], 2) }} {{ $row['currency'] ?? '' }}
                                        @elseif($column['type'] === 'number')
                                            {{ number_format($row[$key], 2) }} {{ $row[$key . '_suffix'] ?? $column['suffix'] ?? '' }}
                                        @else
                                            {{ $row[$key] }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            @php
                                foreach($reportData['report']->getTotalColumns() as $col) {
                                    $totals[$col] += $row[$col] ?? 0;
                                }
                            @endphp
                        @endforeach

                        @if($reportData['report']->shouldShowTotals() && !empty($totals))
                            <tr class="total-row">
                                @php
                                    $firstColumnSpan = count($reportData['report']->getColumns()) - count($reportData['report']->getTotalColumns());
                                    $firstColumn = true;
                                    $currency = $reportData['data']->first()['currency'] ?? \Gopos\Models\Currency::getBaseCurrency()?->symbol;
                                @endphp
                                @foreach($reportData['report']->getColumns() as $key => $column)
                                    @if($firstColumn)
                                        <td colspan="{{ $firstColumnSpan }}" class="text-start">{{ __('Total') }}</td>
                                        @php $firstColumn = false; @endphp
                                    @elseif(in_array($key, $reportData['report']->getTotalColumns()))
                                        <td class="text-start">
                                            @if($column['type'] === 'currency')
                                                {{ number_format($totals[$key], 2) }} {{ $currency }}
                                            @elseif($column['type'] === 'number')
                                                {{ number_format($totals[$key], 2) }} {{ $column['suffix'] ?? '' }}
                                            @else
                                                {{ $totals[$key] }} {{ $column['suffix'] ?? '' }}
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endif
                    @endif
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>
