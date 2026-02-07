<div>
    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
        {{ __('Select Modules') }}
    </h2>
    <p class="text-gray-600 dark:text-gray-400 mb-6">
        {{ __('Choose which modules to enable for your business. You can change this later in settings.') }}
    </p>

    {{-- Module Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($availableModules as $key => $module)
            <label class="relative cursor-pointer group">
                <input type="checkbox"
                       wire:model.live="selectedModules"
                       value="{{ $key }}"
                       class="peer sr-only">
                <div class="p-4 rounded-xl border-2 transition-all duration-200
                            peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20
                            border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800
                            hover:border-purple-300 dark:hover:border-purple-700">
                    <div class="flex items-start gap-4">
                        {{-- Checkbox indicator --}}
                        <div class="flex-shrink-0 w-6 h-6 rounded-md border-2 transition-all
                                    peer-checked:border-purple-500 peer-checked:bg-purple-500
                                    border-gray-300 dark:border-gray-600
                                    flex items-center justify-center
                                    {{ in_array($key, $selectedModules) ? 'border-purple-500 bg-purple-500' : '' }}">
                            @if(in_array($key, $selectedModules))
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-semibold text-gray-800 dark:text-gray-200">
                                    @if($locale === 'ar')
                                        {{ $module['name_ar'] }}
                                    @elseif($locale === 'ckb')
                                        {{ $module['name_ckb'] }}
                                    @else
                                        {{ $module['name'] }}
                                    @endif
                                </h3>
                                @if($module['default'])
                                    <span class="text-xs px-2 py-0.5 bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400 rounded-full">
                                        {{ __('Recommended') }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                @if($locale === 'ar')
                                    {{ $module['description_ar'] }}
                                @elseif($locale === 'ckb')
                                    {{ $module['description_ckb'] }}
                                @else
                                    {{ $module['description'] }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </label>
        @endforeach
    </div>

    {{-- Selection Summary --}}
    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-gray-700 dark:text-gray-300">
                {{ __('Selected modules') }}:
            </span>
            <span class="font-semibold text-purple-600 dark:text-purple-400">
                {{ count($selectedModules) }} / {{ count($availableModules) }}
            </span>
        </div>
    </div>

    {{-- Note --}}
    <div class="mt-4 flex items-start gap-3 text-sm text-gray-500 dark:text-gray-400">
        <svg class="w-5 h-5 flex-shrink-0 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p>
            {{ __('Modules can be enabled or disabled at any time from the application settings. Disabled modules will hide their menu items and features.') }}
        </p>
    </div>
</div>
