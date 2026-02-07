<x-filament-panels::page>
    {{-- Current Session Summary --}}
    @if ($this->hasOpenSession())
        <div class="mb-6">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-clock class="h-5 w-5 text-gray-500" />
                            {{ __('Current Shift') }}
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                            {{ __('Open') }}
                        </span>
                    </div>
                </x-slot>

                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Opened At') }}</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $currentSession->opening_time->format('H:i') }}
                        </div>
                        <div class="text-xs text-gray-400">
                            {{ $currentSession->opening_time->format('Y-m-d') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Opening Cash') }}</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ number_format($currentSession->opening_cash, 2, '.', ',') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Sales Count') }}</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $currentSession->sales_count }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Expected Cash') }}</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ number_format($this->getExpectedCash(), 2, '.', ',') }}
                        </div>
                    </div>
                </div>

                @if ($currentSession->terminal_id)
                    <div class="mt-4 text-sm text-gray-500">
                        {{ __('Terminal') }}: {{ $currentSession->terminal_id }}
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap gap-2">
                    <x-filament::button wire:click="recordCashIn" color="gray" size="sm">
                        <x-heroicon-o-plus class="h-4 w-4 mr-1" />
                        {{ __('Cash In') }}
                    </x-filament::button>
                    <x-filament::button wire:click="recordCashOut" color="gray" size="sm">
                        <x-heroicon-o-minus class="h-4 w-4 mr-1" />
                        {{ __('Cash Out') }}
                    </x-filament::button>
                    <x-filament::button wire:click="closeShift" color="danger" size="sm">
                        <x-heroicon-o-lock-closed class="h-4 w-4 mr-1" />
                        {{ __('Close Shift') }}
                    </x-filament::button>
                </div>
            </x-filament::section>
        </div>
    @else
        <div class="mb-6">
            <x-filament::section>
                <div class="py-6 text-center">
                    <x-heroicon-o-clock class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" />
                    <h3 class="mt-3 text-base font-medium text-gray-900 dark:text-gray-100">
                        {{ __('No Active Shift') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Open a shift to start processing sales.') }}
                    </p>
                    <div class="mt-4">
                        <x-filament::button wire:click="openShift" color="primary">
                            <x-heroicon-o-play class="h-4 w-4 mr-1" />
                            {{ __('Open Shift') }}
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        </div>
    @endif

    {{-- Session History Table --}}
    <x-filament::section>
        <x-slot name="heading">{{ __('Shift History') }}</x-slot>
        {{ $this->table }}
    </x-filament::section>

    {{-- Open Shift Modal --}}
    @if($showOpenModal)
        <div
            x-data="{ open: true }"
            x-show="open"
            x-on:keydown.escape.window="$wire.cancelOpenModal()"
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity" wire:click="cancelOpenModal"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative inline-block w-full max-w-md transform overflow-hidden rounded-xl bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:align-middle"
                >
                    <div class="px-6 pb-4 pt-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-500/20">
                                <x-heroicon-o-play class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Open Shift') }}</h3>
                        </div>

                        <div class="mt-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Opening Cash') }}
                                </label>
                                <div class="mt-1">
                                    <input
                                        type="number"
                                        wire:model="openingCash"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Terminal ID') }}
                                    <span class="text-gray-400 font-normal">({{ __('Optional') }})</span>
                                </label>
                                <div class="mt-1">
                                    <input
                                        type="text"
                                        wire:model="terminalId"
                                        placeholder="{{ __('e.g., POS-1') }}"
                                        class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex justify-end gap-3">
                        <x-filament::button wire:click="cancelOpenModal" color="gray">
                            {{ __('Cancel') }}
                        </x-filament::button>
                        <x-filament::button wire:click="confirmOpenShift" color="primary">
                            {{ __('Open Shift') }}
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Close Shift Modal --}}
    @if($showCloseModal && $currentSession)
        <div
            x-data="{ open: true }"
            x-show="open"
            x-on:keydown.escape.window="$wire.cancelCloseModal()"
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity" wire:click="cancelCloseModal"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative inline-block w-full max-w-lg transform overflow-hidden rounded-xl bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:align-middle"
                >
                    <div class="px-6 pb-4 pt-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-danger-100 dark:bg-danger-500/20">
                                <x-heroicon-o-lock-closed class="h-5 w-5 text-danger-600 dark:text-danger-400" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Close Shift') }}</h3>
                        </div>

                        <div class="mt-6 space-y-4">
                            {{-- Summary Stats --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-3">
                                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Opening Cash') }}</div>
                                    <div class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ number_format($currentSession->opening_cash, 2, '.', ',') }}</div>
                                </div>
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-3">
                                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Expected Cash') }}</div>
                                    <div class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ number_format($this->getExpectedCash(), 2, '.', ',') }}</div>
                                </div>
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-3">
                                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Sales Count') }}</div>
                                    <div class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ $currentSession->sales_count }}</div>
                                </div>
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-3">
                                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Total Sales') }}</div>
                                    <div class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ number_format($currentSession->total_sales_amount, 2, '.', ',') }}</div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Counted Cash') }}
                                </label>
                                <div class="mt-1">
                                    <input
                                        type="number"
                                        wire:model.live="closingCash"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500"
                                    />
                                </div>
                            </div>

                            @if ($closingCash > 0)
                                @php $difference = $this->getCashDifference(); @endphp
                                <div class="flex items-center justify-between rounded-lg p-3 {{ $difference >= 0 ? 'bg-green-50 dark:bg-green-500/10' : 'bg-red-50 dark:bg-red-500/10' }}">
                                    <span class="text-sm font-medium {{ $difference >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">{{ __('Difference') }}</span>
                                    <span class="text-lg font-bold {{ $difference >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                                        {{ $difference >= 0 ? '+' : '' }}{{ number_format($difference, 2, '.', ',') }}
                                    </span>
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Notes') }}
                                    <span class="text-gray-400 font-normal">({{ __('Optional') }})</span>
                                </label>
                                <div class="mt-1">
                                    <textarea
                                        wire:model="closeNotes"
                                        rows="2"
                                        placeholder="{{ __('Any notes...') }}"
                                        class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex justify-end gap-3">
                        <x-filament::button wire:click="cancelCloseModal" color="gray">
                            {{ __('Cancel') }}
                        </x-filament::button>
                        <x-filament::button wire:click="confirmCloseShift" color="danger">
                            {{ __('Close Shift') }}
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Cash In Modal --}}
    @if($showCashInModal)
        <div
            x-data="{ open: true }"
            x-show="open"
            x-on:keydown.escape.window="$wire.cancelCashInModal()"
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity" wire:click="cancelCashInModal"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative inline-block w-full max-w-md transform overflow-hidden rounded-xl bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:align-middle"
                >
                    <div class="px-6 pb-4 pt-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-500/20">
                                <x-heroicon-o-plus class="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Cash In') }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Add cash to the drawer') }}</p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-4">
                            @if ($this->hasOpenSession())
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-3">
                                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Current Expected Cash') }}</div>
                                    <div class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ number_format($this->getExpectedCash(), 2, '.', ',') }}</div>
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Amount') }}
                                </label>
                                <div class="mt-1">
                                    <input
                                        type="number"
                                        wire:model="cashInAmount"
                                        step="0.01"
                                        min="0.01"
                                        placeholder="0.00"
                                        class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Notes') }}
                                    <span class="text-gray-400 font-normal">({{ __('Optional') }})</span>
                                </label>
                                <div class="mt-1">
                                    <textarea
                                        wire:model="cashInNotes"
                                        rows="2"
                                        placeholder="{{ __('e.g., Change fund from bank...') }}"
                                        class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex justify-end gap-3">
                        <x-filament::button wire:click="cancelCashInModal" color="gray">
                            {{ __('Cancel') }}
                        </x-filament::button>
                        <x-filament::button wire:click="confirmCashIn" color="success">
                            {{ __('Record Cash In') }}
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Cash Out Modal --}}
    @if($showCashOutModal)
        <div
            x-data="{ open: true }"
            x-show="open"
            x-on:keydown.escape.window="$wire.cancelCashOutModal()"
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity" wire:click="cancelCashOutModal"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative inline-block w-full max-w-md transform overflow-hidden rounded-xl bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:align-middle"
                >
                    <div class="px-6 pb-4 pt-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-500/20">
                                <x-heroicon-o-minus class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Cash Out') }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Remove cash from drawer') }}</p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-4">
                            @if ($this->hasOpenSession())
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-3">
                                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Current Expected Cash') }}</div>
                                    <div class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ number_format($this->getExpectedCash(), 2, '.', ',') }}</div>
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Amount') }}
                                </label>
                                <div class="mt-1">
                                    <input
                                        type="number"
                                        wire:model="cashOutAmount"
                                        step="0.01"
                                        min="0.01"
                                        placeholder="0.00"
                                        class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Reason / Notes') }}
                                    <span class="text-gray-400 font-normal">({{ __('Optional') }})</span>
                                </label>
                                <div class="mt-1">
                                    <textarea
                                        wire:model="cashOutNotes"
                                        rows="2"
                                        placeholder="{{ __('e.g., Bank deposit...') }}"
                                        class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex justify-end gap-3">
                        <x-filament::button wire:click="cancelCashOutModal" color="gray">
                            {{ __('Cancel') }}
                        </x-filament::button>
                        <x-filament::button wire:click="confirmCashOut" color="warning">
                            {{ __('Record Cash Out') }}
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
