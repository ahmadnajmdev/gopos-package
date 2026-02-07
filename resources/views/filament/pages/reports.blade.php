<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filters Card --}}
        <div >
            {{ $this->form }}
        </div>

        @if($this->data && isset($this->data['reportType']))
            @php
                $data = $this->getReportData();
                $report = $this->getReportInstance();
                $locale = app()->getLocale();
                $rtlLocales = ['ar','ckb'];
                $isRtl = in_array($locale, $rtlLocales);
                $direction = $isRtl ? 'rtl' : 'ltr';
                $formData = $this->form->getState();
            @endphp

            {{-- Report Header Card --}}
            <div class="bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 rounded-xl shadow-sm border border-primary-200 dark:border-primary-700 p-6">
                <div class="flex items-center justify-between" dir="{{ $direction }}">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ __($report->getTitle()) }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            <span class="inline-flex items-center">
                                <svg class="w-4 h-4 {{ $isRtl ? 'ml-1' : 'mr-1' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ $formData['startDate'] }} - {{ $formData['endDate'] }}
                            </span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(is_array($data) && isset($data['rows']))
                            <div class="px-5 py-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Total Entries') }}</span>
                                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ count($data['rows']) }}</div>
                            </div>
                        @else
                            <div class="px-5 py-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Total Entries') }}</span>
                                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $data->count() }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Report Table Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto" dir="{{ $direction }}">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                        {{-- Table Header --}}
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                @foreach($report->getColumns() as $key => $column)
                                    <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __($column['label']) }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        {{-- Table Body --}}
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @if(is_array($data) && isset($data['rows']))
                                {{-- Financial Report Style --}}
                                @foreach($data['rows'] as $row)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 {{ $row['is_total'] ?? false ? 'bg-primary-50 dark:bg-primary-900/20 border-t-2 border-primary-200 dark:border-primary-700' : ($row['is_subtotal'] ?? false ? 'bg-gray-100 dark:bg-gray-700/50' : '') }}">
                                        @foreach($report->getColumns() as $key => $column)
                                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $row['is_total'] ?? false ? 'font-bold' : ($row['is_subtotal'] ?? false ? 'font-semibold' : 'font-medium') }} {{ $key === 'amount' && isset($row['amount']) && $row['amount'] < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }} {{ $key === 'amount' && isset($row['amount']) && $row['amount'] >= 0 && ($row['is_total'] ?? false) ? 'text-green-600 dark:text-green-400' : '' }}">
                                                @if($column['type'] === 'currency')
                                                    <span class="inline-flex items-center">
                                                        @if(isset($row[$key]) && $row[$key] < 0)
                                                            <svg class="w-4 h-4 {{ $isRtl ? 'ml-1' : 'mr-1' }}" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" />
                                                            </svg>
                                                        @elseif(isset($row[$key]) && $row[$key] > 0 && ($row['is_total'] ?? false))
                                                            <svg class="w-4 h-4 {{ $isRtl ? 'ml-1' : 'mr-1' }}" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                                                            </svg>
                                                        @endif
                                                        <span class="tabular-nums">{{ number_format($row[$key], 2) }}</span>
                                                        <span class="text-gray-500 dark:text-gray-400 {{ $isRtl ? 'mr-1' : 'ml-1' }}">{{ $row['currency'] ?? '' }}</span>
                                                    </span>
                                                @elseif($column['type'] === 'number')
                                                    <span class="tabular-nums">{{ number_format($row[$key], 2) }}</span>
                                                @else
                                                    <span>{{ $row[$key] }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @else
                                {{-- Standard Table Report Style --}}
                                @php
                                    $totals = [];
                                    foreach($report->getTotalColumns() as $col) {
                                        $totals[$col] = 0;
                                    }
                                @endphp

                                @foreach($data as $index => $row)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 {{ $index % 2 === 0 ? '' : 'bg-gray-50/50 dark:bg-gray-800/50' }}">
                                        @foreach($report->getColumns() as $key => $column)
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                @if($column['type'] === 'currency')
                                                    <span class="inline-flex items-center">
                                                        <span class="tabular-nums">{{ number_format($row[$key], 2) }}</span>
                                                        <span class="text-gray-500 dark:text-gray-400 {{ $isRtl ? 'mr-1' : 'ml-1' }}">{{ $row['currency'] ?? '' }}</span>
                                                    </span>
                                                @elseif($column['type'] === 'number')
                                                    <span class="tabular-nums">{{ number_format($row[$key], 2) }}</span>
                                                @elseif($column['type'] === 'date')
                                                    <span class="inline-flex items-center text-gray-600 dark:text-gray-400">
                                                        <svg class="w-4 h-4 {{ $isRtl ? 'ml-1.5' : 'mr-1.5' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        {{ $row[$key] }}
                                                    </span>
                                                @else
                                                    <span>{{ $row[$key] }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                    @php
                                        foreach($report->getTotalColumns() as $col) {
                                            $totals[$col] += $row[$col] ?? 0;
                                        }
                                    @endphp
                                @endforeach

                                {{-- Totals Row --}}
                                @if($report->shouldShowTotals() && !empty($totals))
                                    <tr class="bg-primary-50 dark:bg-primary-900/20 border-t-2 border-primary-200 dark:border-primary-700">
                                        @php
                                            $firstColumnSpan = count($report->getColumns()) - count($report->getTotalColumns());
                                            $firstColumn = true;
                                            $currency = $data->first()['currency'] ?? \Gopos\Models\Currency::getBaseCurrency()?->symbol;
                                        @endphp
                                        @foreach($report->getColumns() as $key => $column)
                                            @if($firstColumn)
                                                <td colspan="{{ $firstColumnSpan }}" class="px-6 py-4 text-start text-sm font-bold text-gray-900 dark:text-white uppercase">
                                                    <span class="inline-flex items-center">
                                                        <svg class="w-5 h-5 {{ $isRtl ? 'ml-2' : 'mr-2' }} text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                                                        </svg>
                                                        {{ __('Total') }}
                                                    </span>
                                                </td>
                                                @php $firstColumn = false; @endphp
                                            @elseif(in_array($key, $report->getTotalColumns()))
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                                    <span class="inline-flex items-center">
                                                        <span class="tabular-nums">{{ number_format($totals[$key], 2) }}</span>
                                                        <span class="text-gray-500 dark:text-gray-400 {{ $isRtl ? 'mr-1' : 'ml-1' }}">{{ $currency }}</span>
                                                    </span>
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endif
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Empty State --}}
                @if(
                    (is_array($data) && isset($data['rows']) && empty($data['rows'])) ||
                    (!is_array($data) && $data->isEmpty())
                )
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('No data found') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Try adjusting your date range or filters.') }}</p>
                    </div>
                @endif
            </div>

            {{-- Report Footer Info --}}
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 {{ $isRtl ? 'ml-1.5' : 'mr-1.5' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ __('Generated on') }}: {{ now()->format('Y-m-d H:i:s') }}</span>
                    </div>
                    <div class="flex items-center gap-4">
                        @if(!is_array($data) || !isset($data['rows']))
                            <span class="inline-flex items-center">
                                <svg class="w-4 h-4 {{ $isRtl ? 'ml-1.5' : 'mr-1.5' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ $data->count() }} {{ __('records') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @else

            {{-- No Report Selected State --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12">
                <div class="text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">{{ __('Select Report Options') }}</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Choose a report type and date range to generate your report.') }}</p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
