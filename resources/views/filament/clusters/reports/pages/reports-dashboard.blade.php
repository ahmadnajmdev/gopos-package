<x-filament-panels::page>
    @php
        $locale = app()->getLocale();
        $rtlLocales = ['ar','ckb'];
        $isRtl = in_array($locale, $rtlLocales);
        $direction = $isRtl ? 'rtl' : 'ltr';
    @endphp

    <div class="space-y-8" dir="{{ $direction }}">
        {{-- Header Section --}}
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

        {{-- Report Categories --}}
        @foreach($this->getReportCategories() as $category)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                {{-- Category Header --}}
                <div class="bg-gradient-to-r from-{{ $category['color'] }}-50 to-{{ $category['color'] }}-100 dark:from-{{ $category['color'] }}-900/20 dark:to-{{ $category['color'] }}-800/20 border-b border-{{ $category['color'] }}-200 dark:border-{{ $category['color'] }}-700 p-6">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-14 h-14 bg-{{ $category['color'] }}-500 dark:bg-{{ $category['color'] }}-600 rounded-xl flex items-center justify-center shadow-lg">
                                <x-filament::icon
                                    :icon="$category['icon']"
                                    class="w-8 h-8 text-white"
                                />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ $category['title'] }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ $category['description'] }}
                            </p>
                        </div>
                        <div class="text-{{ $category['color'] }}-600 dark:text-{{ $category['color'] }}-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isRtl ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7' }}" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Reports Grid --}}
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($category['reports'] as $report)
                            <a href="{{ $report['url'] }}"
                               class="group relative bg-gray-50 dark:bg-gray-900/50 hover:bg-{{ $category['color'] }}-50 dark:hover:bg-{{ $category['color'] }}-900/30 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-{{ $category['color'] }}-500 dark:hover:border-{{ $category['color'] }}-600 transition-all duration-200 p-5 cursor-pointer shadow-sm hover:shadow-md">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-{{ $category['color'] }}-100 dark:bg-{{ $category['color'] }}-900/50 group-hover:bg-{{ $category['color'] }}-500 dark:group-hover:bg-{{ $category['color'] }}-600 rounded-lg flex items-center justify-center transition-colors duration-200">
                                            <x-filament::icon
                                                :icon="$report['icon']"
                                                class="w-5 h-5 text-{{ $category['color'] }}-600 dark:text-{{ $category['color'] }}-400 group-hover:text-white transition-colors duration-200"
                                            />
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-{{ $category['color'] }}-600 dark:group-hover:text-{{ $category['color'] }}-400 transition-colors duration-200">
                                            {{ $report['title'] }}
                                        </h3>
                                    </div>
                                    <div class="text-gray-400 group-hover:text-{{ $category['color'] }}-500 transition-colors duration-200">
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
        @endforeach

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">{{ __('Total Report Categories') }}</p>
                        <p class="text-3xl font-bold mt-2">{{ count($this->getReportCategories()) }}</p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 dark:from-green-600 dark:to-green-700 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">{{ __('Available Reports') }}</p>
                        <p class="text-3xl font-bold mt-2">
                            {{ collect($this->getReportCategories())->sum(fn($cat) => count($cat['reports'])) }}
                        </p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 dark:from-purple-600 dark:to-purple-700 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">{{ __('Export Formats') }}</p>
                        <p class="text-3xl font-bold mt-2">PDF</p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
