<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Introduction Card --}}
        <div class="bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 rounded-xl shadow-sm border border-primary-200 dark:border-primary-700 p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ __('Bulk Export') }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Select multiple reports and generate a combined PDF with all reports in a single file. All selected reports will use the same date range.') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            {{ $this->form }}
        </div>

        {{-- Info Card --}}
        @if($this->data && !empty($this->data['reports']))
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-700 p-4">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                            {{ __('Ready to export') }}
                            <span class="font-bold">{{ count($this->data['reports']) }}</span>
                            {{ trans_choice('report|reports', count($this->data['reports'])) }}
                        </p>
                        <p class="mt-1 text-xs text-blue-700 dark:text-blue-300">
                            {{ __('Click "Generate Combined PDF" in the header to download your reports.') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
