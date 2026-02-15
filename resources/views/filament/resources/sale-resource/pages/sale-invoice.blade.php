<x-filament-panels::page>

    <div id="invoice" wire:ignore>
        @livewire('sale-invoice', ['sale' => $sale])
    </div>

    @if($sale->isInstallment() && $sale->installments->isNotEmpty())
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 mt-6">
            <h3 class="text-lg font-semibold mb-4">{{ __('Installment Schedule') }}</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-start">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="px-3 py-2 text-start font-medium text-gray-500 dark:text-gray-400">#</th>
                            <th class="px-3 py-2 text-start font-medium text-gray-500 dark:text-gray-400">{{ __('Due Date') }}</th>
                            <th class="px-3 py-2 text-start font-medium text-gray-500 dark:text-gray-400">{{ __('Amount') }}</th>
                            <th class="px-3 py-2 text-start font-medium text-gray-500 dark:text-gray-400">{{ __('Paid') }}</th>
                            <th class="px-3 py-2 text-start font-medium text-gray-500 dark:text-gray-400">{{ __('Remaining') }}</th>
                            <th class="px-3 py-2 text-start font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->installments->sortBy('installment_number') as $installment)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-3 py-2">{{ $installment->installment_number }}</td>
                                <td class="px-3 py-2">{{ $installment->due_date->format('Y-m-d') }}</td>
                                <td class="px-3 py-2">{{ number_format($installment->amount, 2) }}</td>
                                <td class="px-3 py-2">{{ number_format($installment->paid_amount, 2) }}</td>
                                <td class="px-3 py-2">{{ number_format($installment->remaining, 2) }}</td>
                                <td class="px-3 py-2">
                                    <span @class([
                                        'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset',
                                        'bg-yellow-50 text-yellow-700 ring-yellow-600/20 dark:bg-yellow-400/10 dark:text-yellow-500 dark:ring-yellow-400/20' => $installment->status === \Gopos\Enums\InstallmentStatus::Pending,
                                        'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-400/10 dark:text-green-500 dark:ring-green-400/20' => $installment->status === \Gopos\Enums\InstallmentStatus::Paid,
                                        'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-500 dark:ring-red-400/20' => $installment->status === \Gopos\Enums\InstallmentStatus::Overdue,
                                        'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-400/10 dark:text-blue-500 dark:ring-blue-400/20' => $installment->status === \Gopos\Enums\InstallmentStatus::Partial,
                                    ])>
                                        {{ $installment->status->getLabel() }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @script
    <script>
        // Print invoice function
        window.printInvoice = function() {
            var printContents = document.getElementById('invoice').innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        };

        // Thermal Receipt Print Function - opens in new tab with preview
        window.printThermalReceipt = function(html) {
            // Open in a new tab
            const printTab = window.open('', '_blank');

            if (!printTab) {
                alert('{{ __("Please allow popups for printing receipts") }}');
                return;
            }

            const receiptHtml = `
                <!DOCTYPE html>
                <html dir="{{ app()->getLocale() === 'ar' || app()->getLocale() === 'ckb' ? 'rtl' : 'ltr' }}">
                <head>
                    <meta charset="UTF-8">
                    <title>{{ __('Receipt') }} - {{ config('app.name') }}</title>
                    <style>
                        @@page {
                            size: 80mm auto;
                            margin: 0;
                        }
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }
                        html {
                            background: #f3f4f6;
                        }
                        body {
                            font-family: 'Courier New', 'Lucida Console', monospace;
                            font-size: 12px;
                            width: 80mm;
                            max-width: 80mm;
                            margin: 20px auto;
                            padding: 5mm;
                            background: white;
                            color: black;
                            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                            border-radius: 4px;
                        }
                        .receipt {
                            width: 100% !important;
                            max-width: 74mm !important;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        td, th {
                            padding: 2px 0;
                            vertical-align: top;
                        }
                        hr {
                            border: none;
                            border-top: 1px dashed #000;
                            margin: 8px 0;
                        }
                        h2 {
                            font-size: 16px;
                            margin-bottom: 5px;
                            text-align: center;
                        }
                        .actions {
                            width: 80mm;
                            max-width: 80mm;
                            margin: 0 auto 20px;
                            display: flex;
                            gap: 10px;
                        }
                        .print-btn {
                            flex: 1;
                            padding: 12px 20px;
                            background: #4F46E5;
                            color: white;
                            border: none;
                            border-radius: 8px;
                            cursor: pointer;
                            font-size: 14px;
                            font-weight: 500;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                        }
                        .print-btn:hover {
                            background: #4338CA;
                        }
                        .close-btn {
                            padding: 12px 20px;
                            background: #6B7280;
                            color: white;
                            border: none;
                            border-radius: 8px;
                            cursor: pointer;
                            font-size: 14px;
                            font-weight: 500;
                        }
                        .close-btn:hover {
                            background: #4B5563;
                        }
                        @@media print {
                            html {
                                background: white;
                            }
                            body {
                                width: 80mm;
                                max-width: 80mm;
                                margin: 0;
                                box-shadow: none;
                                border-radius: 0;
                            }
                            .no-print {
                                display: none !important;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="no-print actions">
                        <button class="print-btn" onclick="window.print()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                            {{ __('Print Receipt') }}
                        </button>
                        <button class="close-btn" onclick="window.close()">{{ __('Close') }}</button>
                    </div>
                    ${html}
                </body>
                </html>
            `;

            printTab.document.write(receiptHtml);
            printTab.document.close();

            // Auto-trigger print dialog when the page loads
            printTab.onload = function() {
                printTab.print();
            };
        };

        // Listen for the Livewire event from the action
        $wire.on('print-thermal-receipt', (event) => {
            window.printThermalReceipt(event.html);
        });
    </script>
    @endscript
</x-filament-panels::page>
