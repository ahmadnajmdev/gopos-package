<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Customer Information Header --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4 ">
                        @if($this->getRecord()->image)
                            <img class="h-16 w-16 rounded-full" src="{{ Storage::url($this->getRecord()->image) }}" alt="{{ $this->getRecord()->name }}">
                        @else
                            <div class="h-16 w-16 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                <svg class="h-8 w-8 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->getRecord()->name }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $this->getRecord()->email }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $this->getRecord()->phone }}</p>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        @php
            $summary = $this->getCustomerSummary();
            $baseCurrencySymbol = \Gopos\Models\Currency::getBaseCurrency()?->symbol;
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="ml-5 rtl:ml-0 rtl:mr-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ __('Total Invoices') }}</dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ $summary['total_invoices'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                        <div class="ml-5 rtl:ml-0 rtl:mr-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ __('Total Purchase Amount') }}</dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ number_format($summary['total_sales'], 0) }} {{ $baseCurrencySymbol }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 rtl:ml-0 rtl:mr-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ __('Total Paid') }}</dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ number_format($summary['total_paid'], 0) }} {{ $baseCurrencySymbol }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 {{ $summary['total_balance'] > 0 ? 'text-red-400' : 'text-green-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 rtl:ml-0 rtl:mr-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ __('Outstanding Balance') }}</dt>
                                <dd class="text-lg font-medium {{ $summary['total_balance'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                    {{ number_format($summary['total_balance'], 0) }} {{ $baseCurrencySymbol }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Breakdown --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-3 w-3 bg-green-400 rounded-full"></div>
                        </div>
                        <div class="ml-3 rtl:ml-0 rtl:mr-3">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Paid Invoices') }}</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $summary['paid_invoices'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-3 w-3 bg-yellow-400 rounded-full"></div>
                        </div>
                        <div class="ml-3 rtl:ml-0 rtl:mr-3">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Partially Paid') }}</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $summary['partial_invoices'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-3 w-3 bg-red-400 rounded-full"></div>
                        </div>
                        <div class="ml-3 rtl:ml-0 rtl:mr-3">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Unpaid Invoices') }}</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $summary['unpaid_invoices'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Sales Table --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('Sales Transactions') }}</h3>
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
