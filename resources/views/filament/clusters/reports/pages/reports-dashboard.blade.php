<x-filament-panels::page>
    @php
        $locale = app()->getLocale();
        $rtlLocales = ['ar','ckb'];
        $isRtl = in_array($locale, $rtlLocales);
        $direction = $isRtl ? 'rtl' : 'ltr';
        $currency = $this->getCurrency();

        $salesKpis = $this->getSalesKpis();
        $purchaseKpis = $this->getPurchaseKpis();
        $inventoryKpis = $this->getInventoryKpis();
        $customerKpis = $this->getCustomerKpis();
        $financialKpis = $this->getFinancialKpis();
        $sparkline = $this->getSalesSparklineData();

        $max = max($sparkline) ?: 1;
        $sparklinePoints = collect($sparkline)->map(function ($val, $i) use ($max) {
            $x = $i * (100 / 6);
            $y = 30 - ($val / $max * 26) - 2;
            return round($x, 1) . ',' . round($y, 1);
        })->implode(' ');
    @endphp

    <div class="space-y-6" dir="{{ $direction }}">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-primary-500 to-primary-700 dark:from-primary-800 dark:to-primary-900 rounded-2xl shadow-lg p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        {{ __('Reports & Analytics') }}
                    </h1>
                    <p class="mt-2 text-lg text-primary-100">
                        {{ __('Access comprehensive business reports and insights') }}
                    </p>
                </div>
                <div class="hidden lg:block">
                    <svg class="w-24 h-24 text-white opacity-20" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Sales Reports --}}
        @php $category = $this->getReportCategories()[0]; @endphp
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-success-50 to-success-100 dark:from-success-900/20 dark:to-success-800/20 border-b border-success-200 dark:border-success-700 p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-14 h-14 bg-success-500 dark:bg-success-600 rounded-xl flex items-center justify-center shadow-lg">
                            <x-filament::icon icon="heroicon-o-shopping-cart" class="w-8 h-8 text-white" />
                        </div>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $category['title'] }}</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $category['description'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Sales KPIs --}}
            <div class="px-6 pt-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                        <div class="flex-shrink-0 w-9 h-9 bg-success-100 dark:bg-success-900/30 rounded-lg flex items-center justify-center">
                            <x-filament::icon icon="heroicon-o-banknotes" class="w-5 h-5 text-success-600 dark:text-success-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ __("Today's Sales") }}</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ number_format($salesKpis['todays_sales'], 2) }}
                                <span class="text-xs font-normal text-gray-400">{{ $currency }}</span>
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <svg viewBox="0 0 100 30" class="w-20 h-7" preserveAspectRatio="none">
                                <polyline
                                    points="{{ $sparklinePoints }}"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="text-success-500"
                                />
                            </svg>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                        <div class="flex-shrink-0 w-9 h-9 bg-success-100 dark:bg-success-900/30 rounded-lg flex items-center justify-center">
                            <x-filament::icon icon="heroicon-o-receipt-percent" class="w-5 h-5 text-success-600 dark:text-success-400" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ __('Sales This Month') }}</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($salesKpis['this_month_count']) }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                        <div class="flex-shrink-0 w-9 h-9 {{ $salesKpis['month_growth'] >= 0 ? 'bg-success-100 dark:bg-success-900/30' : 'bg-danger-100 dark:bg-danger-900/30' }} rounded-lg flex items-center justify-center">
                            <x-filament::icon
                                :icon="$salesKpis['month_growth'] >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down'"
                                class="w-5 h-5 {{ $salesKpis['month_growth'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}"
                            />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ __('MoM Growth') }}</p>
                            <p class="text-sm font-bold {{ $salesKpis['month_growth'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                                {{ $salesKpis['month_growth'] >= 0 ? '+' : '' }}{{ $salesKpis['month_growth'] }}%
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($category['reports'] as $report)
                        <a href="{{ $report['url'] }}"
                           class="group relative bg-gray-50 dark:bg-gray-900/50 hover:bg-success-50 dark:hover:bg-success-900/30 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-success-500 dark:hover:border-success-600 transition-all duration-200 p-5 cursor-pointer shadow-sm hover:shadow-md">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-success-100 dark:bg-success-900/50 group-hover:bg-success-500 dark:group-hover:bg-success-600 rounded-lg flex items-center justify-center transition-colors duration-200">
                                        <x-filament::icon :icon="$report['icon']" class="w-5 h-5 text-success-600 dark:text-success-400 group-hover:text-white transition-colors duration-200" />
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-success-600 dark:group-hover:text-success-400 transition-colors duration-200">
                                        {{ $report['title'] }}
                                    </h3>
                                </div>
                                <div class="text-gray-400 group-hover:text-success-500 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isRtl ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7' }}" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Purchase Reports --}}
        @php $category = $this->getReportCategories()[1]; @endphp
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-info-50 to-info-100 dark:from-info-900/20 dark:to-info-800/20 border-b border-info-200 dark:border-info-700 p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-14 h-14 bg-info-500 dark:bg-info-600 rounded-xl flex items-center justify-center shadow-lg">
                            <x-filament::icon icon="heroicon-o-shopping-bag" class="w-8 h-8 text-white" />
                        </div>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $category['title'] }}</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $category['description'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Purchase KPIs --}}
            <div class="px-6 pt-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                        <div class="flex-shrink-0 w-9 h-9 bg-info-100 dark:bg-info-900/30 rounded-lg flex items-center justify-center">
                            <x-filament::icon icon="heroicon-o-shopping-cart" class="w-5 h-5 text-info-600 dark:text-info-400" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ __('Purchases This Month') }}</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ number_format($purchaseKpis['this_month_total'], 2) }}
                                <span class="text-xs font-normal text-gray-400">{{ $currency }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                        <div class="flex-shrink-0 w-9 h-9 bg-warning-100 dark:bg-warning-900/30 rounded-lg flex items-center justify-center">
                            <x-filament::icon icon="heroicon-o-clock" class="w-5 h-5 text-warning-600 dark:text-warning-400" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ __('Outstanding Payables') }}</p>
                            <p class="text-sm font-bold text-warning-600 dark:text-warning-400">
                                {{ number_format($purchaseKpis['outstanding_payables'], 2) }}
                                <span class="text-xs font-normal text-gray-400">{{ $currency }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($category['reports'] as $report)
                        <a href="{{ $report['url'] }}"
                           class="group relative bg-gray-50 dark:bg-gray-900/50 hover:bg-info-50 dark:hover:bg-info-900/30 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-info-500 dark:hover:border-info-600 transition-all duration-200 p-5 cursor-pointer shadow-sm hover:shadow-md">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-info-100 dark:bg-info-900/50 group-hover:bg-info-500 dark:group-hover:bg-info-600 rounded-lg flex items-center justify-center transition-colors duration-200">
                                        <x-filament::icon :icon="$report['icon']" class="w-5 h-5 text-info-600 dark:text-info-400 group-hover:text-white transition-colors duration-200" />
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-info-600 dark:group-hover:text-info-400 transition-colors duration-200">
                                        {{ $report['title'] }}
                                    </h3>
                                </div>
                                <div class="text-gray-400 group-hover:text-info-500 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isRtl ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7' }}" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Inventory Reports --}}
        @php $category = $this->getReportCategories()[2]; @endphp
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-warning-50 to-warning-100 dark:from-warning-900/20 dark:to-warning-800/20 border-b border-warning-200 dark:border-warning-700 p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-14 h-14 bg-warning-500 dark:bg-warning-600 rounded-xl flex items-center justify-center shadow-lg">
                            <x-filament::icon icon="heroicon-o-cube" class="w-8 h-8 text-white" />
                        </div>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $category['title'] }}</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $category['description'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Inventory KPIs --}}
            <div class="px-6 pt-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                        <div class="flex-shrink-0 w-9 h-9 bg-warning-100 dark:bg-warning-900/30 rounded-lg flex items-center justify-center">
                            <x-filament::icon icon="heroicon-o-calculator" class="w-5 h-5 text-warning-600 dark:text-warning-400" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ __('Stock Value') }}</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ number_format($inventoryKpis['total_stock_value'], 2) }}
                                <span class="text-xs font-normal text-gray-400">{{ $currency }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                        <div class="flex-shrink-0 w-9 h-9 {{ $inventoryKpis['low_stock_count'] > 0 ? 'bg-danger-100 dark:bg-danger-900/30' : 'bg-success-100 dark:bg-success-900/30' }} rounded-lg flex items-center justify-center">
                            <x-filament::icon
                                icon="heroicon-o-exclamation-triangle"
                                class="w-5 h-5 {{ $inventoryKpis['low_stock_count'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}"
                            />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ __('Low Stock') }}</p>
                            <p class="text-sm font-bold {{ $inventoryKpis['low_stock_count'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
                                {{ $inventoryKpis['low_stock_count'] }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                        <div class="flex-shrink-0 w-9 h-9 {{ $inventoryKpis['expiring_count'] > 0 ? 'bg-danger-100 dark:bg-danger-900/30' : 'bg-success-100 dark:bg-success-900/30' }} rounded-lg flex items-center justify-center">
                            <x-filament::icon
                                icon="heroicon-o-clock"
                                class="w-5 h-5 {{ $inventoryKpis['expiring_count'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}"
                            />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ __('Expiring Soon') }}</p>
                            <p class="text-sm font-bold {{ $inventoryKpis['expiring_count'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
                                {{ $inventoryKpis['expiring_count'] }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($category['reports'] as $report)
                        <a href="{{ $report['url'] }}"
                           class="group relative bg-gray-50 dark:bg-gray-900/50 hover:bg-warning-50 dark:hover:bg-warning-900/30 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-warning-500 dark:hover:border-warning-600 transition-all duration-200 p-5 cursor-pointer shadow-sm hover:shadow-md">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-warning-100 dark:bg-warning-900/50 group-hover:bg-warning-500 dark:group-hover:bg-warning-600 rounded-lg flex items-center justify-center transition-colors duration-200">
                                        <x-filament::icon :icon="$report['icon']" class="w-5 h-5 text-warning-600 dark:text-warning-400 group-hover:text-white transition-colors duration-200" />
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-warning-600 dark:group-hover:text-warning-400 transition-colors duration-200">
                                        {{ $report['title'] }}
                                    </h3>
                                </div>
                                <div class="text-gray-400 group-hover:text-warning-500 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isRtl ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7' }}" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Customer Reports --}}
        @php $category = $this->getReportCategories()[3]; @endphp
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 border-b border-primary-200 dark:border-primary-700 p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-14 h-14 bg-primary-500 dark:bg-primary-600 rounded-xl flex items-center justify-center shadow-lg">
                            <x-filament::icon icon="heroicon-o-users" class="w-8 h-8 text-white" />
                        </div>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $category['title'] }}</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $category['description'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Customer KPIs --}}
            <div class="px-6 pt-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                        <div class="flex-shrink-0 w-9 h-9 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center">
                            <x-filament::icon icon="heroicon-o-banknotes" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ __('Outstanding Balances') }}</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ number_format($customerKpis['outstanding_balances'], 2) }}
                                <span class="text-xs font-normal text-gray-400">{{ $currency }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                        <div class="flex-shrink-0 w-9 h-9 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center">
                            <x-filament::icon icon="heroicon-o-user-group" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ __('Active Customers') }}</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $customerKpis['active_customers'] }}</p>
                            <p class="text-[10px] text-gray-400">{{ __('Last 30 days') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($category['reports'] as $report)
                        <a href="{{ $report['url'] }}"
                           class="group relative bg-gray-50 dark:bg-gray-900/50 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-600 transition-all duration-200 p-5 cursor-pointer shadow-sm hover:shadow-md">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/50 group-hover:bg-primary-500 dark:group-hover:bg-primary-600 rounded-lg flex items-center justify-center transition-colors duration-200">
                                        <x-filament::icon :icon="$report['icon']" class="w-5 h-5 text-primary-600 dark:text-primary-400 group-hover:text-white transition-colors duration-200" />
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-200">
                                        {{ $report['title'] }}
                                    </h3>
                                </div>
                                <div class="text-gray-400 group-hover:text-primary-500 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isRtl ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7' }}" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Financial Reports --}}
        @php $category = $this->getReportCategories()[4]; @endphp
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-danger-50 to-danger-100 dark:from-danger-900/20 dark:to-danger-800/20 border-b border-danger-200 dark:border-danger-700 p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-14 h-14 bg-danger-500 dark:bg-danger-600 rounded-xl flex items-center justify-center shadow-lg">
                            <x-filament::icon icon="heroicon-o-chart-bar" class="w-8 h-8 text-white" />
                        </div>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $category['title'] }}</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $category['description'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Financial KPIs --}}
            <div class="px-6 pt-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                        <div class="flex-shrink-0 w-9 h-9 bg-danger-100 dark:bg-danger-900/30 rounded-lg flex items-center justify-center">
                            <x-filament::icon icon="heroicon-o-currency-dollar" class="w-5 h-5 text-danger-600 dark:text-danger-400" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ __('Revenue This Month') }}</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ number_format($financialKpis['revenue'], 2) }}
                                <span class="text-xs font-normal text-gray-400">{{ $currency }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                        <div class="flex-shrink-0 w-9 h-9 {{ $financialKpis['net_profit'] >= 0 ? 'bg-success-100 dark:bg-success-900/30' : 'bg-danger-100 dark:bg-danger-900/30' }} rounded-lg flex items-center justify-center">
                            <x-filament::icon
                                icon="heroicon-o-chart-bar"
                                class="w-5 h-5 {{ $financialKpis['net_profit'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}"
                            />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ __('Net Profit') }}</p>
                            <p class="text-sm font-bold {{ $financialKpis['net_profit'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                                {{ number_format($financialKpis['net_profit'], 2) }}
                                <span class="text-xs font-normal text-gray-400">{{ $currency }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                        <div class="flex-shrink-0 w-9 h-9 bg-danger-100 dark:bg-danger-900/30 rounded-lg flex items-center justify-center">
                            <x-filament::icon icon="heroicon-o-presentation-chart-line" class="w-5 h-5 text-danger-600 dark:text-danger-400" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ __('Gross Margin') }}</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $financialKpis['gross_margin'] }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($category['reports'] as $report)
                        <a href="{{ $report['url'] }}"
                           class="group relative bg-gray-50 dark:bg-gray-900/50 hover:bg-danger-50 dark:hover:bg-danger-900/30 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-danger-500 dark:hover:border-danger-600 transition-all duration-200 p-5 cursor-pointer shadow-sm hover:shadow-md">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-danger-100 dark:bg-danger-900/50 group-hover:bg-danger-500 dark:group-hover:bg-danger-600 rounded-lg flex items-center justify-center transition-colors duration-200">
                                        <x-filament::icon :icon="$report['icon']" class="w-5 h-5 text-danger-600 dark:text-danger-400 group-hover:text-white transition-colors duration-200" />
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-danger-600 dark:group-hover:text-danger-400 transition-colors duration-200">
                                        {{ $report['title'] }}
                                    </h3>
                                </div>
                                <div class="text-gray-400 group-hover:text-danger-500 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isRtl ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7' }}" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
