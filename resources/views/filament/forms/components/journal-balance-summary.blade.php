<div
    x-data="{
        lines: $wire.$entangle('data.lines', true),
        currencySymbol: '{{ $getCurrencySymbol() }}',

        get totalDebit() {
            if (!this.lines || typeof this.lines !== 'object') return 0;
            return Object.values(this.lines).reduce((sum, line) => {
                return sum + (parseFloat(line?.debit) || 0);
            }, 0);
        },

        get totalCredit() {
            if (!this.lines || typeof this.lines !== 'object') return 0;
            return Object.values(this.lines).reduce((sum, line) => {
                return sum + (parseFloat(line?.credit) || 0);
            }, 0);
        },

        get difference() {
            return this.totalDebit - this.totalCredit;
        },

        get isBalanced() {
            return Math.abs(this.difference) < 0.01;
        },

        formatNumber(num) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        }
    }"
    class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900"
>
    <div class="mb-3 flex items-center gap-2">
        <svg class="h-5 w-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Balance Summary') }}</h3>
    </div>

    <div class="grid grid-cols-3 gap-4">
        {{-- Total Debit --}}
        <div class="rounded-lg bg-emerald-50 p-3 dark:bg-emerald-900/20">
            <div class="text-xs font-medium uppercase tracking-wide text-emerald-600 dark:text-emerald-400">
                {{ __('Total Debit') }}
            </div>
            <div class="mt-1 text-xl font-bold tabular-nums text-emerald-700 dark:text-emerald-300" x-text="formatNumber(totalDebit) + ' ' + currencySymbol">
                0.00
            </div>
        </div>

        {{-- Total Credit --}}
        <div class="rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20">
            <div class="text-xs font-medium uppercase tracking-wide text-blue-600 dark:text-blue-400">
                {{ __('Total Credit') }}
            </div>
            <div class="mt-1 text-xl font-bold tabular-nums text-blue-700 dark:text-blue-300" x-text="formatNumber(totalCredit) + ' ' + currencySymbol">
                0.00
            </div>
        </div>

        {{-- Balance Status --}}
        <div
            class="rounded-lg p-3 transition-colors duration-200"
            :class="isBalanced ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20'"
        >
            <div
                class="text-xs font-medium uppercase tracking-wide"
                :class="isBalanced ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
            >
                {{ __('Balance') }}
            </div>
            <div class="mt-1 flex items-center gap-2">
                {{-- Balanced icon --}}
                <template x-if="isBalanced">
                    <div class="flex items-center gap-1.5">
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-lg font-bold text-green-700 dark:text-green-300">{{ __('Balanced') }}</span>
                    </div>
                </template>

                {{-- Unbalanced icon --}}
                <template x-if="!isBalanced">
                    <div class="flex items-center gap-1.5">
                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-semibold text-red-700 dark:text-red-300">
                            <span x-show="difference > 0">{{ __('Debit') }} +<span x-text="formatNumber(Math.abs(difference))"></span></span>
                            <span x-show="difference < 0">{{ __('Credit') }} +<span x-text="formatNumber(Math.abs(difference))"></span></span>
                        </span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Visual balance bar --}}
    <div class="mt-4">
        <div class="relative h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
            <div
                class="absolute left-0 top-0 h-full rounded-full bg-emerald-500 transition-all duration-300"
                :style="{
                    width: totalDebit === 0 && totalCredit === 0
                        ? '50%'
                        : Math.min((totalDebit / Math.max(totalDebit + totalCredit, 1)) * 100, 100) + '%'
                }"
            ></div>
            <div
                class="absolute right-0 top-0 h-full rounded-full bg-blue-500 transition-all duration-300"
                :style="{
                    width: totalDebit === 0 && totalCredit === 0
                        ? '50%'
                        : Math.min((totalCredit / Math.max(totalDebit + totalCredit, 1)) * 100, 100) + '%'
                }"
            ></div>
        </div>
        <div class="mt-1 flex justify-between text-xs text-gray-500 dark:text-gray-400">
            <span>{{ __('Debit') }}</span>
            <span x-show="isBalanced" class="font-medium text-green-600 dark:text-green-400">{{ __('Equal') }}</span>
            <span>{{ __('Credit') }}</span>
        </div>
    </div>
</div>
