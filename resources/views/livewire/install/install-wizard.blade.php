<div class="min-h-screen py-8 px-4">
    <div class="max-w-4xl mx-auto">
        {{-- Language Selector --}}
        <div class="flex justify-end mb-4">
            <div class="flex gap-2 bg-white/80 dark:bg-gray-800/80 rounded-lg p-1 shadow-sm">
                <button wire:click="setLocale('en')"
                    class="px-3 py-1.5 rounded-md text-sm font-medium transition {{ $locale === 'en' ? 'bg-purple-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-purple-100 dark:hover:bg-purple-900/30' }}">
                    English
                </button>
                <button wire:click="setLocale('ar')"
                    class="px-3 py-1.5 rounded-md text-sm font-medium transition {{ $locale === 'ar' ? 'bg-purple-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-purple-100 dark:hover:bg-purple-900/30' }}">
                    العربية
                </button>
                <button wire:click="setLocale('ckb')"
                    class="px-3 py-1.5 rounded-md text-sm font-medium transition {{ $locale === 'ckb' ? 'bg-purple-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-purple-100 dark:hover:bg-purple-900/30' }}">
                    کوردی
                </button>
            </div>
        </div>

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-600 rounded-2xl mb-4 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-purple-800 dark:text-purple-200">
                {{ __('GoPOS Installation') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                {{ __('Complete the following steps to set up your application') }}
            </p>
        </div>

        {{-- Progress Indicator --}}
        <div class="mb-8">
            <div class="flex items-center justify-between relative">
                {{-- Progress Line --}}
                <div class="absolute top-5 left-0 right-0 h-0.5 bg-gray-300 dark:bg-gray-700"></div>
                <div class="absolute top-5 left-0 h-0.5 bg-purple-600 transition-all duration-300" style="width: {{ (($step - 1) / 5) * 100 }}%"></div>

                @php
                    $steps = [
                        1 => ['name' => __('Requirements'), 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        2 => ['name' => __('Database'), 'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4'],
                        3 => ['name' => __('Modules'), 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                        4 => ['name' => __('Admin'), 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                        5 => ['name' => __('Business'), 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                        6 => ['name' => __('Install'), 'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'],
                    ];
                @endphp

                @foreach($steps as $stepNum => $stepInfo)
                    <div class="flex flex-col items-center relative z-10">
                        <button
                            wire:click="goToStep({{ $stepNum }})"
                            @if($stepNum > $step) disabled @endif
                            class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 {{ $step >= $stepNum ? 'bg-purple-600 text-white shadow-lg' : 'bg-white dark:bg-gray-800 text-gray-400 border-2 border-gray-300 dark:border-gray-600' }} {{ $stepNum <= $step ? 'cursor-pointer hover:scale-110' : 'cursor-not-allowed' }}">
                            @if($step > $stepNum)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stepInfo['icon'] }}"></path>
                                </svg>
                            @endif
                        </button>
                        <span class="text-xs mt-2 font-medium {{ $step >= $stepNum ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $stepInfo['name'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Step Content --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-6">
            @switch($step)
                @case(1)
                    @include('gopos::livewire.install.partials.step-welcome')
                    @break
                @case(2)
                    @include('gopos::livewire.install.partials.step-database')
                    @break
                @case(3)
                    @include('gopos::livewire.install.partials.step-modules')
                    @break
                @case(4)
                    @include('gopos::livewire.install.partials.step-admin')
                    @break
                @case(5)
                    @include('gopos::livewire.install.partials.step-business')
                    @break
                @case(6)
                    @include('gopos::livewire.install.partials.step-finalize')
                    @break
            @endswitch
        </div>

        {{-- Navigation Buttons --}}
        @if(!$installationComplete)
        <div class="flex justify-between">
            <button
                wire:click="previousStep"
                @if($step === 1) disabled @endif
                class="px-6 py-2.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl shadow hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed transition font-medium flex items-center gap-2">
                <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('Previous') }}
            </button>

            @if($step < 6)
                <button
                    wire:click="nextStep"
                    class="px-6 py-2.5 bg-purple-600 text-white rounded-xl shadow hover:shadow-md hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition font-medium flex items-center gap-2">
                    {{ __('Next') }}
                    <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            @else
                @if(!$installationFailed)
                    <button
                        wire:click="runInstallation"
                        wire:loading.attr="disabled"
                        @if($isInstalling) disabled @endif
                        class="px-6 py-2.5 bg-green-600 text-white rounded-xl shadow hover:shadow-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition font-medium flex items-center gap-2">
                        <span wire:loading wire:target="runInstallation">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="runInstallation">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                        </span>
                        {{ __('Run Installation') }}
                    </button>
                @endif
            @endif
        </div>
        @endif
    </div>
</div>
