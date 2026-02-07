<x-filament-panels::page>
    @if ($session)
        {{-- Header Info --}}
        <div class="mb-6 flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ __('Z-Report / Shift Summary') }}
                </h2>
                <p class="text-gray-500 dark:text-gray-400">
                    {{ __('Cashier') }}: {{ $session->user?->name ?? 'N/A' }}
                    @if ($session->terminal_id)
                        | {{ __('Terminal') }}: {{ $session->terminal_id }}
                    @endif
                </p>
            </div>
            <x-filament::button
                tag="a"
                href="{{ route('filament.admin.pages.pos-shift-management') }}"
                color="gray"
            >
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                {{ __('Back to Shifts') }}
            </x-filament::button>
        </div>

        {{-- Session Times --}}
        <x-filament::section class="mb-6">
            <x-slot name="heading">{{ __('Session Details') }}</x-slot>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Opened') }}</div>
                    <div class="text-lg font-semibold">
                        {{ $session->opening_time->format('Y-m-d H:i') }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Closed') }}</div>
                    <div class="text-lg font-semibold">
                        {{ $session->closing_time?->format('Y-m-d H:i') ?? '-' }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Duration') }}</div>
                    <div class="text-lg font-semibold">
                        @if ($session->closing_time)
                            {{ $session->opening_time->diffForHumans($session->closing_time, true) }}
                        @else
                            {{ $session->opening_time->diffForHumans(now(), true) }}
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Status') }}</div>
                    <div>
                        <x-filament::badge :color="$session->status === 'open' ? 'success' : ($session->status === 'suspended' ? 'warning' : 'gray')">
                            {{ __(ucfirst($session->status)) }}
                        </x-filament::badge>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Cash Summary --}}
        <x-filament::section class="mb-6">
            <x-slot name="heading">{{ __('Cash Summary') }}</x-slot>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Opening Cash') }}</div>
                    <div class="text-2xl font-bold">
                        {{ number_format($session->opening_cash, 2) }}
                    </div>
                </div>

                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="text-sm text-blue-600 dark:text-blue-400">{{ __('Cash Sales') }}</div>
                    <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                        +{{ number_format($summary['cash_movements']['sales'] ?? 0, 2) }}
                    </div>
                </div>

                <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <div class="text-sm text-red-600 dark:text-red-400">{{ __('Cash Refunds') }}</div>
                    <div class="text-2xl font-bold text-red-700 dark:text-red-300">
                        -{{ number_format($summary['cash_movements']['refunds'] ?? 0, 2) }}
                    </div>
                </div>

                <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <div class="text-sm text-green-600 dark:text-green-400">{{ __('Expected Cash') }}</div>
                    <div class="text-2xl font-bold text-green-700 dark:text-green-300">
                        {{ number_format($summary['expected_cash'] ?? 0, 2) }}
                    </div>
                </div>
            </div>

            @if ($session->closing_cash !== null)
                <div class="mt-6 grid grid-cols-2 gap-6">
                    <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Counted Cash') }}</div>
                        <div class="text-2xl font-bold">
                            {{ number_format($session->closing_cash, 2) }}
                        </div>
                    </div>

                    <div class="p-4 rounded-lg {{ $session->cash_difference >= 0 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }}">
                        <div class="text-sm {{ $session->cash_difference >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ __('Cash Difference') }}
                        </div>
                        <div class="text-2xl font-bold {{ $session->cash_difference >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            {{ $session->cash_difference >= 0 ? '+' : '' }}{{ number_format($session->cash_difference, 2) }}
                        </div>
                    </div>
                </div>
            @endif
        </x-filament::section>

        {{-- Sales Summary --}}
        <x-filament::section class="mb-6">
            <x-slot name="heading">{{ __('Sales Summary') }}</x-slot>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Transactions') }}</div>
                    <div class="text-3xl font-bold">{{ $summary['sales_count'] ?? 0 }}</div>
                </div>

                <div class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                    <div class="text-sm text-primary-600 dark:text-primary-400">{{ __('Total Sales') }}</div>
                    <div class="text-3xl font-bold text-primary-700 dark:text-primary-300">
                        {{ number_format($summary['total_sales'] ?? 0, 2) }}
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Average Sale') }}</div>
                    <div class="text-3xl font-bold">
                        @if (($summary['sales_count'] ?? 0) > 0)
                            {{ number_format(($summary['total_sales'] ?? 0) / $summary['sales_count'], 2) }}
                        @else
                            0.00
                        @endif
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Payment Methods Breakdown --}}
        @if (!empty($summary['by_payment_method']))
            <x-filament::section class="mb-6">
                <x-slot name="heading">{{ __('Payment Methods') }}</x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="text-start py-2 px-4">{{ __('Method') }}</th>
                                <th class="text-end py-2 px-4">{{ __('Transactions') }}</th>
                                <th class="text-end py-2 px-4">{{ __('Sales') }}</th>
                                <th class="text-end py-2 px-4">{{ __('Refunds') }}</th>
                                <th class="text-end py-2 px-4">{{ __('Net') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($summary['by_payment_method'] as $method => $data)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="py-2 px-4 font-medium">
                                        {{ __(ucfirst(str_replace('_', ' ', $method))) }}
                                    </td>
                                    <td class="text-end py-2 px-4">{{ $data['count'] }}</td>
                                    <td class="text-end py-2 px-4 text-success-600">
                                        {{ number_format($data['sales'], 2) }}
                                    </td>
                                    <td class="text-end py-2 px-4 text-danger-600">
                                        {{ number_format($data['refunds'], 2) }}
                                    </td>
                                    <td class="text-end py-2 px-4 font-bold">
                                        {{ number_format($data['net'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 dark:bg-gray-800 font-bold">
                                <td class="py-2 px-4">{{ __('Total') }}</td>
                                <td class="text-end py-2 px-4">
                                    {{ collect($summary['by_payment_method'])->sum('count') }}
                                </td>
                                <td class="text-end py-2 px-4 text-success-600">
                                    {{ number_format(collect($summary['by_payment_method'])->sum('sales'), 2) }}
                                </td>
                                <td class="text-end py-2 px-4 text-danger-600">
                                    {{ number_format(collect($summary['by_payment_method'])->sum('refunds'), 2) }}
                                </td>
                                <td class="text-end py-2 px-4">
                                    {{ number_format(collect($summary['by_payment_method'])->sum('net'), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-filament::section>
        @endif

        {{-- Notes --}}
        @if ($session->notes)
            <x-filament::section>
                <x-slot name="heading">{{ __('Notes') }}</x-slot>
                <p class="text-gray-700 dark:text-gray-300">{{ $session->notes }}</p>
            </x-filament::section>
        @endif

        {{-- Print Button --}}
        <div class="mt-6 flex justify-end">
            <x-filament::button onclick="window.print()">
                <x-heroicon-o-printer class="w-4 h-4 mr-2" />
                {{ __('Print Report') }}
            </x-filament::button>
        </div>
    @else
        <div class="text-center py-12">
            <x-heroicon-o-exclamation-circle class="w-12 h-12 mx-auto text-gray-400" />
            <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Session Not Found') }}
            </h3>
        </div>
    @endif
</x-filament-panels::page>
