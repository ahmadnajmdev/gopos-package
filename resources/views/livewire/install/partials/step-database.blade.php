<div>
    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
        {{ __('Database Connection') }}
    </h2>
    <p class="text-gray-600 dark:text-gray-400 mb-6">
        {{ __('Please verify that your database is properly configured.') }}
    </p>

    {{-- Connection Status --}}
    <div class="mb-6">
        <div class="p-6 rounded-xl {{ $databaseConnected ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' }}">
            <div class="flex items-start gap-4">
                @if($databaseConnected)
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-green-800 dark:text-green-200 mb-2">
                            {{ __('Connection Successful') }}
                        </h3>
                        <div class="space-y-2 text-sm text-green-700 dark:text-green-300">
                            <p><span class="font-medium">{{ __('Driver') }}:</span> {{ $databaseStatus['driver'] ?? 'N/A' }}</p>
                            <p><span class="font-medium">{{ __('Database') }}:</span> {{ $databaseStatus['database'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                @else
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-2">
                            {{ __('Connection Failed') }}
                        </h3>
                        <p class="text-sm text-red-700 dark:text-red-300 mb-4">
                            {{ $databaseStatus['message'] ?? __('Unable to connect to database') }}
                        </p>
                        <div class="bg-red-100 dark:bg-red-900/40 rounded-lg p-4 text-sm">
                            <p class="font-medium text-red-800 dark:text-red-200 mb-2">{{ __('Please check:') }}</p>
                            <ul class="list-disc list-inside space-y-1 text-red-700 dark:text-red-300">
                                <li>{{ __('Database server is running') }}</li>
                                <li>{{ __('Database credentials in .env file') }}</li>
                                <li>{{ __('Database name exists') }}</li>
                                <li>{{ __('Network connectivity') }}</li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Configuration Info --}}
    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ __('Configuration Location') }}
        </h4>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('Database settings are configured in the') }} <code class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-purple-600 dark:text-purple-400">.env</code> {{ __('file in your application root.') }}
        </p>
    </div>

    {{-- Refresh Button --}}
    @if(!$databaseConnected)
        <div class="mt-6 text-center">
            <button wire:click="refreshDatabase" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                {{ __('Retry Connection') }}
            </button>
        </div>
    @endif
</div>
