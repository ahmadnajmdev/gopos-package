<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ckb' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Customer Statement') }} - {{ $customer->name }}</title>
    <style>
        @font-face {
            font-family: 'Rabar';
            src: url('{{ asset("css/fonts/Rabar_021.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        /* Reset and base */
        :root{
            --brand:#0a6efd;
            --muted:#6b7280;
            --bg:#f8fafc;
            --card:#ffffff;
            --accent:#0f172a;
            --danger:#dc2626;
            font-family: 'Rabar', Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, 'DejaVu Sans', sans-serif;
        }
        *{box-sizing:border-box}
        html,body{height:100%}
        body{
            margin:0; padding:24px; background:var(--bg); color:var(--accent); -webkit-font-smoothing:antialiased;
        }

        /* Container */
        .statement-wrap{max-width:900px;margin:0 auto}
        .card{background:var(--card);border-radius:12px;padding:28px;box-shadow:0 6px 18px rgba(12,14,20,0.06)}

        header{display:flex;justify-content:space-between;align-items:flex-start;gap:20px}
        .brand{display:flex;gap:16px;align-items:center}
        .logo{
            width:84px;height:84px;border-radius:8px;background:linear-gradient(135deg,var(--brand),#6b21a8);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:20px
        }
        .company{line-height:1}
        .company h1{margin:0;font-size:18px}
        .company p{margin:2px 0;color:var(--muted);font-size:13px}

        .meta{ text-align:right }
        .meta .title{font-weight:700}
        .meta p{margin:4px 0;color:var(--muted);font-size:13px}

        /* Two column section */
        .row{display:flex;gap:20px;margin-top:22px}
        .col{flex:1}
        .col.small{flex:0 0 260px}
        .box{background:linear-gradient(90deg,transparent,transparent);padding:16px;border-radius:8px}

        .section-title{font-weight:700;margin-bottom:8px}
        .info p{margin:6px 0;color:var(--muted);font-size:13px}

        /* Transactions table */
        table{width:100%;border-collapse:collapse;margin-top:18px}
        thead th{background:#f1f5f9;padding:10px 12px;text-align:left;font-size:13px;color:var(--muted);border-bottom:1px solid #e6eef8}
        tbody td{padding:12px;border-bottom:1px solid #f1f5f9;font-size:14px}
        tbody tr:nth-child(even){background:rgba(15,23,42,0.02)}

        .amount{white-space:nowrap;text-align:right}

        /* Totals */
        .totals{display:flex;justify-content:flex-end;margin-top:12px}
        .totals .inner{width:320px}
        .totals-row{display:flex;justify-content:space-between;padding:8px 12px}
        .totals-row.total{font-weight:800;font-size:18px}

        /* Footer */
        footer{margin-top:22px;color:var(--muted);font-size:13px}

        /* Responsive */
        @media (max-width:720px){
            header{flex-direction:column;align-items:flex-start}
            .meta{text-align:left}
            .row{flex-direction:column}
            .col.small{flex:1}
            .totals{justify-content:space-between}
            .totals .inner{width:100%}
        }

        /* Print-friendly */
        @media print{
            body{background:white;padding:0}
            .statement-wrap{margin:0}
            .card{box-shadow:none;border-radius:0;padding:16mm}
            a[href]:after{content:""}
        }

        /* Utility */
        .muted{color:var(--muted)}
        .pill{display:inline-block;padding:6px 10px;border-radius:999px;font-size:12px;background:#eef2ff;color:var(--brand)}

        /* Small helpers */
        .right{text-align:right}

        /* Status colors */
        .status-paid {
            color: #28a745;
            font-weight: bold;
        }

        .status-partial {
            color: #ffc107;
            font-weight: bold;
        }

        .status-unpaid {
            color: #dc3545;
            font-weight: bold;
        }

        .balance-positive {
            color: #dc3545;
            font-weight: bold;
        }

        .balance-zero {
            color: #28a745;
            font-weight: bold;
        }

        /* RTL Support */
        [dir="rtl"] .meta {
            text-align: left;
        }

        [dir="rtl"] .right {
            text-align: left;
        }

        [dir="rtl"] .amount {
            text-align: left;
        }

        [dir="rtl"] table th {
            text-align: right;
        }

        [dir="rtl"] table th.right {
            text-align: left;
        }

        [dir="rtl"] table td {
            text-align: right;
        }

        [dir="rtl"] table td.amount {
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="statement-wrap">
        <div class="card">
            <header>
                <div class="brand">
                    @if(setting('general.logo'))
                        <img src="{{ Storage::url(setting('general.logo')) }}" alt="{{ config('app.name') }}" class="logo" style="width:84px;height:84px;object-fit:contain;border-radius:8px;background:white;padding:8px">
                    @else
                        <div class="logo">{{ substr(config('app.name', 'IM'), 0, 2) }}</div>
                    @endif
                    <div class="company">
                        <h1>{{ setting('invoice.your_company_name') ?? config('app.name', 'Inventory Management') }}</h1>
                        @if(setting('invoice.your_company_address'))
                            <p>{{ setting('invoice.your_company_address') }}</p>
                        @endif
                        @if(setting('invoice.your_company_phone') || setting('invoice.your_company_email'))
                            <p class="muted">
                                @if(setting('invoice.your_company_phone')){{ __('Phone') }}: {{ setting('invoice.your_company_phone') }}@endif
                                @if(setting('invoice.your_company_phone') && setting('invoice.your_company_email')) • @endif
                                @if(setting('invoice.your_company_email')){{ setting('invoice.your_company_email') }}@endif
                            </p>
                        @endif
                    </div>
                </div>

                <div class="meta">
                    <div class="title">{{ __('Customer Statement') }}</div>
                    <p class="muted">{{ __('Statement Date') }}: <strong>{{ now()->format('d-m-Y') }}</strong></p>
                    <p class="muted">{{ __('Account No') }}: <strong>{{ $customer->id }}</strong></p>
                    <p class="muted">{{ __('Period') }}: <strong>{{ \Carbon\Carbon::parse($fromDate)->format('d-m-Y') }} — {{ \Carbon\Carbon::parse($toDate)->format('d-m-Y') }}</strong></p>
                </div>
            </header>

            <div class="row" style="margin-top:18px">
                <div class="col">
                    <div class="box info">
                        <div class="section-title">{{ __('Bill To') }}</div>
                        <p><strong>{{ $customer->name }}</strong></p>
                        @if($customer->address)
                            <p>{{ $customer->address }}</p>
                        @endif
                        @if($customer->email)
                            <p class="muted">{{ __('Email') }}: <span>{{ $customer->email }}</span></p>
                        @endif
                        @if($customer->phone)
                            <p class="muted">{{ __('Phone') }}: <span>{{ $customer->phone }}</span></p>
                        @endif
                    </div>
                </div>

                <div class="col small">
                    <div class="box">
                        <div class="section-title">{{ __('Summary') }}</div>
                        <div style="display:flex;justify-content:space-between;margin-top:8px">
                            <div class="muted">{{ __('Total Invoices') }}</div>
                            <div class="amount">{{ $summary['total_invoices'] }}</div>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-top:6px">
                            <div class="muted">{{ __('Total Sales Amount') }}</div>
                            <div class="amount">{{ number_format($summary['total_sales'], 2) }} {{ $baseCurrency->symbol }}</div>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-top:6px">
                            <div class="muted">{{ __('Total Paid') }}</div>
                            <div class="amount">{{ number_format($summary['total_paid'], 2) }} {{ $baseCurrency->symbol }}</div>
                        </div>
                        <hr style="margin:12px 0;border:none;border-top:1px solid #f1f5f9">
                        <div style="display:flex;justify-content:space-between;font-weight:700">
                            <div>{{ __('Outstanding Balance') }}</div>
                            <div class="amount {{ $summary['total_balance'] > 0 ? 'balance-positive' : 'balance-zero' }}">
                                {{ number_format($summary['total_balance'], 2) }} {{ $baseCurrency->symbol }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <section style="margin-top:20px">
                <div class="section-title">{{ __('Transactions') }}</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width:120px">{{ __('Invoice #') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th style="width:120px" class="right">{{ __('Total Amount') }}</th>
                            <th style="width:120px" class="right">{{ __('Paid Amount') }}</th>
                            <th style="width:120px" class="right">{{ __('Balance') }}</th>
                            <th style="width:120px" class="right">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            @php
                                $balance = $sale->total_amount - $sale->paid_amount;
                                $status = $sale->paid_amount == 0 ? 'unpaid' :
                                         ($sale->paid_amount >= $sale->total_amount ? 'paid' : 'partial');
                            @endphp
                            <tr>
                                <td>{{ $sale->sale_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }}</td>
                                <td class="amount">{{ number_format($sale->total_amount, 2) }} {{ $sale->currency->symbol }}</td>
                                <td class="amount">{{ number_format($sale->paid_amount, 2) }} {{ $sale->currency->symbol }}</td>
                                <td class="amount {{ $balance > 0 ? 'balance-positive' : 'balance-zero' }}">
                                    {{ number_format($balance, 2) }} {{ $sale->currency->symbol }}
                                </td>
                                <td class="amount status-{{ $status }}">
                                    @if($status === 'paid')
                                        {{ __('Paid') }}
                                    @elseif($status === 'partial')
                                        {{ __('Partially Paid') }}
                                    @else
                                        {{ __('Unpaid') }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">{{ __('No transactions found for the selected period.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="totals">
                    <div class="inner">
                        <div class="totals-row">
                            <div class="muted">{{ __('Total Sales') }}</div>
                            <div class="amount">{{ number_format($summary['total_sales'], 2) }} {{ $baseCurrency->symbol }}</div>
                        </div>
                        <div class="totals-row">
                            <div class="muted">{{ __('Total Paid') }}</div>
                            <div class="amount">{{ number_format($summary['total_paid'], 2) }} {{ $baseCurrency->symbol }}</div>
                        </div>
                        <div class="totals-row total">
                            <div>{{ __('Outstanding Balance') }}</div>
                            <div class="amount {{ $summary['total_balance'] > 0 ? 'balance-positive' : 'balance-zero' }}">
                                {{ number_format($summary['total_balance'], 2) }} {{ $baseCurrency->symbol }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <footer>
                <p>{{ __('If you have questions about this statement, please contact our billing department.') }}</p>
                <p class="muted">{{ __('Generated on') }}: {{ now()->format('d-m-Y \a\t H:i') }} | {{ setting('invoice.your_company_name') ?? config('app.name', 'Inventory Management') }} - {{ __('Customer Statement') }}</p>
            </footer>
        </div>
    </div>

     <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };

        // Close window after printing
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>
