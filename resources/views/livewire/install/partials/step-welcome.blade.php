<div>
    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
        {{ __('System Requirements') }}
    </h2>
    <p class="text-gray-600 dark:text-gray-400 mb-6">
        {{ __('Please ensure your server meets the following requirements.') }}
    </p>

    {{-- PHP Version --}}
    <div class="mb-6">
        <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
            </svg>
            {{ __('PHP Version') }}
        </h3>
        <div class="flex items-center gap-3 p-3 rounded-lg {{ $requirements['php_version']['passed'] ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
            @if($requirements['php_version']['passed'])
                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            @else
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            @endif
            <span class="{{ $requirements['php_version']['passed'] ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                {{ __('Required') }}: {{ $requirements['php_version']['required'] }} |
                {{ __('Current') }}: {{ $requirements['php_version']['current'] }}
            </span>
        </div>
    </div>

    {{-- PHP Extensions --}}
    <div class="mb-6">
        <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path>
            </svg>
            {{ __('PHP Extensions') }}
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($requirements['extensions'] as $key => $ext)
                <div class="flex items-center gap-2 p-2 rounded-lg {{ $ext['passed'] ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
                    @if($ext['passed'])
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    @endif
                    <span class="{{ $ext['passed'] ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }} text-sm">
                        {{ $ext['name'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Directory Permissions --}}
    <div class="mb-6">
        <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
            </svg>
            {{ __('Directory Permissions') }}
        </h3>
        <div class="space-y-2">
            @foreach($requirements['directories'] as $key => $dir)
                <div class="flex items-center gap-2 p-2 rounded-lg {{ $dir['passed'] ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
                    @if($dir['passed'])
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    @endif
                    <span class="{{ $dir['passed'] ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }} text-sm font-mono">
                        {{ $dir['path'] }}
                    </span>
                    <span class="text-xs {{ $dir['passed'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        ({{ $dir['passed'] ? __('Writable') : __('Not Writable') }})
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Status --}}
    <div class="p-4 rounded-xl {{ $requirementsPassed ? 'bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800' : 'bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800' }}">
        <div class="flex items-center gap-3">
            @if($requirementsPassed)
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-green-800 dark:text-green-200">
                    {{ __('All requirements are met. You can proceed to the next step.') }}
                </span>
            @else
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="flex-1">
                    <span class="text-red-800 dark:text-red-200">
                        {{ __('Some requirements are not met. Please fix them before continuing.') }}
                    </span>
                    <button wire:click="refreshRequirements" class="ml-2 text-red-600 dark:text-red-400 underline hover:no-underline text-sm">
                        {{ __('Check Again') }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
