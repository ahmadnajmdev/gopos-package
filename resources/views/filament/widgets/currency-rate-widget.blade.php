<x-filament-widgets::widget>
    <div
        x-data="{
            amount: $wire.entangle('pricePerHundred'),
            get ratePerDollar() {
                return this.amount > 0 ? Math.round(this.amount / 100) : 0;
            }
        }"
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4"
    >
        <div class="flex items-center justify-between gap-4 flex-wrap">
            {{-- Left: Icon + Title + Live Rate --}}
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                    <x-heroicon-o-currency-dollar class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white leading-tight">{{ __('USD Exchange Rate') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-tight mt-0.5">
                        1$ = <span x-text="ratePerDollar.toLocaleString()" class="font-semibold text-emerald-600 dark:text-emerald-400"></span> {{ __('IQD') }}
                    </p>
                </div>
            </div>

            {{-- Right: Input + Button --}}
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 whitespace-nowrap">$100 =</span>
                <input
                    type="number"
                    x-model.number="amount"
                    wire:keydown.enter="updateRate"
                    class="fi-input w-32 rounded-lg border-gray-300 bg-gray-50 text-sm font-medium tabular-nums shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 focus:bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500 dark:focus:bg-gray-600"
                    min="1"
                    step="100"
                />
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ __('IQD') }}</span>
                <button
                    wire:click="updateRate"
                    wire:loading.attr="disabled"
                    wire:target="updateRate"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition disabled:opacity-50 whitespace-nowrap"
                >
                    <x-heroicon-m-check wire:loading.remove wire:target="updateRate" class="w-3.5 h-3.5" />
                    <x-filament::loading-indicator wire:loading wire:target="updateRate" class="w-3.5 h-3.5" />
                    {{ __('Save') }}
                </button>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
