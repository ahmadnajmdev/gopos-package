<div>
    @if(!$installationComplete)
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
            {{ __('Ready to Install') }}
        </h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            {{ __('Review your settings and click "Run Installation" to complete the setup.') }}
        </p>

        {{-- Summary --}}
        <div class="space-y-4 mb-6">
            {{-- Modules Summary --}}
            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700">
                <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"></path>
                    </svg>
                    {{ __('Selected Modules') }}
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($selectedModules as $moduleKey)
                        <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 rounded-full text-sm">
                            {{ $availableModules[$moduleKey]['name'] ?? $moduleKey }}
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- Admin Summary --}}
            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700">
                <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    {{ __('Administrator') }}
                </h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">{{ __('Name') }}:</span>
                        <span class="text-gray-800 dark:text-gray-200 ms-2">{{ $adminName }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">{{ __('Email') }}:</span>
                        <span class="text-gray-800 dark:text-gray-200 ms-2">{{ $adminEmail }}</span>
                    </div>
                </div>
            </div>

            {{-- Business Summary --}}
            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700">
                <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"></path>
                    </svg>
                    {{ __('Business') }}
                </h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">{{ __('Name') }}:</span>
                        <span class="text-gray-800 dark:text-gray-200 ms-2">{{ $businessName }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">{{ __('Demo Data') }}:</span>
                        <span class="text-gray-800 dark:text-gray-200 ms-2">{{ $seedDemoData ? __('Yes') : __('No') }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Installation Log --}}
    @if(count($installationLog) > 0)
        <div class="mb-6">
            <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                {{ __('Installation Progress') }}
            </h3>
            <div class="bg-gray-900 rounded-xl p-4 font-mono text-sm max-h-64 overflow-y-auto">
                @foreach($installationLog as $log)
                    <div class="flex items-start gap-3 py-1">
                        <span class="text-gray-500 flex-shrink-0">{{ $log['time'] }}</span>
                        @if($log['type'] === 'success')
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-green-400">{{ $log['message'] }}</span>
                        @elseif($log['type'] === 'error')
                            <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-red-400">{{ $log['message'] }}</span>
                        @else
                            <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span class="text-gray-300">{{ $log['message'] }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Installation Complete --}}
    @if($installationComplete)
        <div class="text-center py-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full mb-6">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-2">
                {{ __('Installation Complete!') }}
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mb-8">
                {{ __('GoPOS has been successfully installed. You can now log in with your admin credentials.') }}
            </p>
            <a href="/"
               class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-xl shadow-lg hover:bg-purple-700 transition font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
                {{ __('Go to Login') }}
            </a>
        </div>
    @endif

    {{-- Installation Failed --}}
    @if($installationFailed)
        <div class="mt-6 p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <h4 class="font-medium text-red-800 dark:text-red-200">
                        {{ __('Installation Failed') }}
                    </h4>
                    <p class="text-sm text-red-600 dark:text-red-400 mt-1">
                        {{ __('Please check the error above and try again. You may need to fix database configuration or file permissions.') }}
                    </p>
                    <button wire:click="runInstallation"
                            class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        {{ __('Retry Installation') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
