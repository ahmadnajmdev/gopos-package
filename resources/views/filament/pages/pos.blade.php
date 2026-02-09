<x-filament-panels::page>
    @if (!$this->hasOpenSession())
        <div class="flex flex-col items-center justify-center py-16 px-6">
            <div class="w-20 h-20 bg-warning-100 dark:bg-warning-900/30 rounded-full flex items-center justify-center mb-6">
                <x-heroicon-o-exclamation-triangle class="w-10 h-10 text-warning-500" />
            </div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                {{ __('No Active Shift') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6 max-w-md">
                {{ __('You need to open a shift before you can use the POS. Please go to Shift Management to start your shift.') }}
            </p>
            <x-filament::button
                tag="a"
                href="{{ \Gopos\Filament\Pages\PosShiftManagement::getUrl() }}"
                icon="heroicon-o-clock"
                color="primary"
                size="lg"
            >
                {{ __('Open Shift') }}
            </x-filament::button>
        </div>
    @else
    <div class="w-full mb-4">
        <div class="flex flex-col md:flex-row gap-4 w-full items-center">
            <!-- Hidden Barcode Input (autofocus) -->
            <x-filament::input.wrapper class="w-full md:w-1/4">
                <x-filament::input type="text" wire:model.live="barcodeInput"
                    wire:keydown.enter.prevent="addToCartByBarcode" autofocus aria-label="{{ __('Barcode') }}"
                    placeholder="{{ __('Scan or enter barcode...') }}" class="w-full" />
            </x-filament::input.wrapper>
            <!-- Search -->
            <x-filament::input.wrapper class="w-full md:w-1/4">
                <x-filament::input type="text" wire:model.live="search" placeholder="{{ __('Search Products...') }}"
                    class="w-full" />
                <x-slot name="suffix">
                    <x-filament::icon-button icon="heroicon-o-magnifying-glass" color="primary" />
                </x-slot>
            </x-filament::input.wrapper>
            <!-- Category -->
            <x-filament::input.wrapper class="w-full md:w-1/4">
                <x-filament::input.select wire:model.live="selectedCategoryId" class="w-full">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
            <!-- Order Column -->
            <x-filament::input.wrapper class="w-full md:w-1/4">
                <x-filament::input.select wire:model.live.debounce.300ms="orderColumn" class="w-full">
                    @foreach ($orderColumnOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
            <!-- Order Direction -->
            <x-filament::input.wrapper class="w-full md:w-1/4">
                <x-filament::input.select wire:model.live="orderDirection" class="w-full">
                    @foreach ($orderDirectionOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>
    </div>

    <div class="flex flex-col md:flex-row items-start gap-4 w-full ">
        <!-- Products Grid Section -->
        <div class="flex flex-col items-start w-full md:w-3/5 gap-4">
            <div class="grid grid-cols-2  lg:grid-cols-3 xl:grid-cols-4 gap-4 w-full">
                @forelse ($products as $product)
                    <button wire:click="addToCart({{ $product->id }})" class="w-full">
                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow flex flex-col h-full min-h-[260px]">
                            <div class="w-full aspect-square mb-2 flex-shrink-0">
                                @if ($product->image)
                                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                                        class="w-full h-full object-cover rounded-lg" style="aspect-ratio: 1 / 1;">
                                @else
                                    <img src="https://placehold.co/400" alt="{{ $product->name }}"
                                        class="w-full h-full object-cover rounded-lg" style="aspect-ratio: 1 / 1;">
                                @endif
                            </div>
                            <div class="flex flex-col flex-1 justify-between">
                                <h2
                                    class="text-start font-semibold text-gray-900 dark:text-white line-clamp-2 text-ellipsis mt-1">
                                    {{ $product->name }}
                                </h2>
                                <div class="flex flex-col items-start gap-1 mt-2">
                                    @php
                                        // Calculate price in selected currency
                                        $currency = $selectedCurrency
                                            ? $currencies->find($selectedCurrency)
                                            : $currencies->where('base', true)->first();
                                        $baseCurrency = $currencies->where('base', true)->first();
                                        $displayPrice = $product->price;

                                        if ($currency && $baseCurrency && $currency->id !== $baseCurrency->id) {
                                            $displayPrice =
                                                ($product->price * $currency->exchange_rate) /
                                                $baseCurrency->exchange_rate;
                                        }
                                    @endphp
                                    <p class="text-primary-600 font-medium">
                                        {{ number_format($displayPrice, $currency->decimal_places ?? 0) }}
                                        {{ $currency->symbol ?? 'IQD' }}
                                    </p>
                                    @if ($product->stock == 0)
                                        <p class="text-red-600 font-medium">
                                            {{ __('Out of stock') }}
                                        </p>
                                    @else
                                        <p class="text-gray-500 font-medium">
                                            {{ $product->stock . ' ' . $product->unit?->abbreviation }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="col-span-2 lg:col-span-3 xl:col-span-4">
                        <div
                            class="flex flex-col items-center justify-center py-12 px-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                            <div
                                class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                                <x-heroicon-o-cube class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                            </div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                                {{ __('No products found') }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                                {{ __('Try adjusting your search or filters') }}
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Cart Section -->
        <div class="w-full md:w-2/5">
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 sticky top-4 overflow-hidden">
                <!-- Cart Header -->
                <div class="bg-gradient-to-r from-primary-500 to-primary-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <x-heroicon-o-shopping-cart class="w-5 h-5" />
                            {{ __('Shopping Cart') }}
                        </h3>
                        <div class="flex items-center gap-3">
                            <span class="bg-white/20 text-white px-2 py-1 rounded-full text-sm font-medium">
                                {{ count($cart) }} {{ __('items') }}
                            </span>
                            @if ($selectedCurrency)
                                @php
                                    $currency = $currencies->find($selectedCurrency);
                                @endphp
                                <span class="bg-white/20 text-white px-2 py-1 rounded-full text-sm font-medium">
                                    {{ $currency->symbol ?? 'IQD' }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                @if (count($cart) > 0)
                    <!-- Cart Items -->
                    <div class="overflow-y-auto max-h-[calc(100vh-28rem)] p-4 space-y-3">
                        @foreach ($cart as $key => $item)
                            @php
                                $subtotal = $item['price'] * $item['stock'];
                                $currency = $selectedCurrency
                                    ? $currencies->find($selectedCurrency)
                                    : $currencies->where('base', true)->first();
                            @endphp
                            <div
                                class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600 hover:shadow-md transition-all duration-200">
                                <div class="flex items-start gap-3">
                                    <!-- Product Image -->
                                    <div class="flex-shrink-0">
                                        <img src="{{ Storage::url($item['image']) }}" alt="{{ $item['name'] }}"
                                            class="w-12 h-12 rounded-lg object-cover border border-gray-200 dark:border-gray-600">
                                    </div>

                                    <!-- Product Details -->
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-medium text-gray-900 dark:text-white text-sm line-clamp-2 mb-1">
                                            {{ $item['name'] }}
                                        </h4>
                                        <div
                                            class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
                                            <span>{{ number_format($item['price'], $currency->decimal_places ?? 0) }}
                                                {{ $currency->symbol ?? 'IQD' }}</span>
                                            <span>•</span>
                                            <span>{{ $item['unit'] }}</span>
                                        </div>

                                        <!-- Quantity Controls -->
                                        <div class="flex items-center justify-between">
                                            <div
                                                class="flex items-center bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-600">
                                                <button wire:click="decreaseCartItem({{ $key }})"
                                                    class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-l-lg transition-colors">
                                                    <x-heroicon-o-minus
                                                        class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                                                </button>
                                                <input type="number" wire:model.live="cart.{{ $key }}.stock"
                                                    value="{{ $item['stock'] }}" min="1"
                                                    class="w-12 text-center text-sm font-medium text-gray-900 dark:text-white bg-transparent border-0 focus:ring-0 focus:outline-none">
                                                <button wire:click="increaseCartItem({{ $key }})"
                                                    class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-r-lg transition-colors">
                                                    <x-heroicon-o-plus
                                                        class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                                                </button>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="font-semibold text-primary-600 dark:text-primary-400 text-sm">
                                                    {{ number_format($subtotal, $currency->decimal_places ?? 0) }}
                                                    {{ $currency->symbol ?? 'IQD' }}
                                                </span>
                                                <button wire:click="removeFromCart({{ $key }})"
                                                    class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                                    <x-heroicon-o-trash class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="p-4 bg-white dark:bg-gray-800">
                        <form>
                            {{ $this->form }}

                            <div
                                class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 p-4 mb-4 mt-4">
                                <div class="space-y-2">
                                    @php
                                        $currency = $selectedCurrency
                                            ? $currencies->find($selectedCurrency)
                                            : $currencies->where('base', true)->first();
                                        $currencySymbol = $currency->symbol ?? 'IQD';
                                        $decimalPlaces = $currency->decimal_places ?? 0;
                                    @endphp
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('Subtotal') }}</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ number_format($formData['sub_total'] ?? 0, $decimalPlaces) }}
                                            {{ $currencySymbol }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-600 dark:text-gray-400">{{ __('Discount') }}</span>
                                            <x-filament::button size="xs" color="gray"
                                                wire:click="openDiscountModal" icon="heroicon-o-tag" class="p-1"
                                                title="{{ __('Apply Discount') }}" />
                                        </div>
                                        <span class="font-medium text-red-600 dark:text-red-400">
                                            -{{ number_format((float) ($formData['discount'] ?? 0), $decimalPlaces) }}
                                            {{ $currencySymbol }}
                                        </span>
                                    </div>
                                    <div class="border-t border-gray-200 dark:border-gray-600 pt-2">
                                        <div class="flex justify-between text-lg font-bold">
                                            <span class="text-gray-900 dark:text-white">{{ __('Total') }}</span>
                                            <span class="text-primary-600 dark:text-primary-400">
                                                {{ number_format($formData['total_amount'] ?? 0, $decimalPlaces) }}
                                                {{ $currencySymbol }}
                                            </span>
                                        </div>
                                    </div>
                                    @if ($currency && !$currency->base)
                                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                                            <span>{{ __('Amount in Base Currency') }}</span>
                                            @php
                                                $baseCurrency = $currencies->where('base', true)->first();
                                            @endphp
                                            <span>
                                                {{ number_format($formData['amount_in_base_currency'] ?? 0, $baseCurrency->decimal_places ?? 0) }}
                                                {{ $baseCurrency->symbol ?? 'IQD' }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col gap-3 mt-4">
                                <!-- Create Invoice Button -->
                                <x-filament::button type="button" wire:click="createInvoice" color="info"
                                    class="w-full">
                                    <div class="flex items-center justify-center gap-2">
                                        <x-heroicon-o-document-text class="w-4 h-4" />
                                        {{ __('Create Invoice') }}
                                    </div>
                                </x-filament::button>

                                <!-- Create Invoice & Payment Button -->
                                <x-filament::button type="button" wire:click="openPaymentModal"
                                    class="w-full bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold py-3 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
                                    <div class="flex items-center justify-center gap-2">
                                        <x-heroicon-o-credit-card class="w-5 h-5" />
                                        {{ __('Create Invoice & Payment') }}
                                    </div>
                                </x-filament::button>
                            </div>
                        </form>
                    </div>
                @else
                    <!-- Empty Cart State -->
                    <div class="flex flex-col items-center justify-center py-12 px-6">
                        <div
                            class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                            <x-heroicon-o-shopping-cart class="w-10 h-10 text-gray-400 dark:text-gray-500" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            {{ __('Your cart is empty') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                            {{ __('Add some products to get started') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <x-filament::pagination :paginator="$products" class="mt-4" />

    <!-- Discount Modal -->
    <x-filament::modal wire:model="showDiscountModal" id="discount-modal">
        <x-slot name="heading">
            {{ __('Apply Discount') }}
        </x-slot>

        <div class="space-y-4">
            @php
                $currency = $selectedCurrency
                    ? $currencies->find($selectedCurrency)
                    : $currencies->where('base', true)->first();
                $currencySymbol = $currency->symbol ?? 'IQD';
                $decimalPlaces = $currency->decimal_places ?? 0;
            @endphp
            <!-- Current Order Summary -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h4 class="font-medium text-gray-900 dark:text-white mb-2">{{ __('Order Summary') }}</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('Subtotal') }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ number_format($formData['sub_total'] ?? 0, $decimalPlaces) }} {{ $currencySymbol }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('Current Discount') }}</span>
                        <span class="font-medium text-red-600 dark:text-red-400">
                            -{{ number_format($formData['discount'] ?? 0, $decimalPlaces) }} {{ $currencySymbol }}
                        </span>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-600 pt-1">
                        <div class="flex justify-between font-semibold">
                            <span class="text-gray-900 dark:text-white">{{ __('Total') }}</span>
                            <span class="text-primary-600 dark:text-primary-400">
                                {{ number_format($formData['total_amount'] ?? 0, $decimalPlaces) }}
                                {{ $currencySymbol }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('Discount Type') }}
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="discountType">
                        <option value="fixed">{{ __('Fixed Amount') }}</option>
                        <option value="percentage">{{ __('Percentage') }}</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('Discount Amount') }}
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input type="number" wire:model.live="discountAmount" step="0.01"
                        min="0" />
                    <x-slot name="suffix">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $discountType === 'percentage' ? '%' : $currencySymbol }}
                        </span>
                    </x-slot>
                </x-filament::input.wrapper>
            </div>

            @if ($discountAmount > 0)
                <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                    <p class="text-sm text-blue-700 dark:text-blue-300 font-medium">
                        {{ __('New Discount Amount') }}:
                        {{ $discountType === 'percentage'
                            ? number_format(($formData['sub_total'] ?? 0) * ($discountAmount / 100), $decimalPlaces) .
                                ' ' .
                                $currencySymbol .
                                ' (' .
                                $discountAmount .
                                '%)'
                            : number_format($discountAmount, $decimalPlaces) . ' ' . $currencySymbol }}
                    </p>
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                        {{ __('New Total') }}:
                        {{ number_format(($formData['sub_total'] ?? 0) - ($discountType === 'percentage' ? ($formData['sub_total'] ?? 0) * ($discountAmount / 100) : $discountAmount), $decimalPlaces) }}
                        {{ $currencySymbol }}
                    </p>
                </div>
            @endif
        </div>

        <x-slot name="footerActions">
            <x-filament::button wire:click="closeDiscountModal" color="gray">
                {{ __('Cancel') }}
            </x-filament::button>
            <x-filament::button wire:click="applyDiscount" color="primary">
                {{ __('Apply Discount') }}
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    <!-- Payment Modal -->
    <x-filament::modal id="payment-modal" width="xl">
        <x-slot name="heading">
            {{ __('Process Payment') }}
        </x-slot>

        <div class="space-y-4">
            @php
                $currency = $selectedCurrency
                    ? $currencies->find($selectedCurrency)
                    : $currencies->where('base', true)->first();
                $currencySymbol = $currency->symbol ?? 'IQD';
                $decimalPlaces = $currency->decimal_places ?? 0;
                $totalAmount = $formData['total_amount'] ?? 0;
            @endphp

            {{-- Total Summary --}}
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600 dark:text-gray-400">{{ __('Total Amount') }}</span>
                    <span class="font-semibold text-gray-900 dark:text-white text-lg">
                        {{ number_format($totalAmount, $decimalPlaces) }} {{ $currencySymbol }}
                    </span>
                </div>
                @if (($formData['discount'] ?? 0) > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('Discount') }}</span>
                        <span class="text-red-600 dark:text-red-400">
                            -{{ number_format($formData['discount'] ?? 0, $decimalPlaces) }} {{ $currencySymbol }}
                        </span>
                    </div>
                @endif
            </div>

            {{-- Split Payment Toggle --}}
            <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <div>
                    <span class="text-sm font-medium text-blue-700 dark:text-blue-300">{{ __('Split Payment') }}</span>
                    <p class="text-xs text-blue-600 dark:text-blue-400">{{ __('Accept multiple payment methods') }}</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.live="useSplitPayment" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-500 peer-checked:bg-blue-600"></div>
                </label>
            </div>

            @if (!$useSplitPayment)
                {{-- Single Payment Mode --}}
                <div class="space-y-4">
                    {{-- Payment Method Selector --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Payment Method') }}
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" wire:click="$set('paymentMethod', 'cash')"
                                class="flex flex-col items-center p-3 rounded-lg border-2 transition-all {{ $paymentMethod === 'cash' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300' }}">
                                <x-heroicon-o-banknotes class="w-6 h-6 {{ $paymentMethod === 'cash' ? 'text-primary-600' : 'text-gray-500' }}" />
                                <span class="text-xs mt-1 {{ $paymentMethod === 'cash' ? 'text-primary-600 font-medium' : 'text-gray-600 dark:text-gray-400' }}">{{ __('Cash') }}</span>
                            </button>
                            <button type="button" wire:click="$set('paymentMethod', 'card')"
                                class="flex flex-col items-center p-3 rounded-lg border-2 transition-all {{ $paymentMethod === 'card' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300' }}">
                                <x-heroicon-o-credit-card class="w-6 h-6 {{ $paymentMethod === 'card' ? 'text-primary-600' : 'text-gray-500' }}" />
                                <span class="text-xs mt-1 {{ $paymentMethod === 'card' ? 'text-primary-600 font-medium' : 'text-gray-600 dark:text-gray-400' }}">{{ __('Card') }}</span>
                            </button>
                            <button type="button" wire:click="$set('paymentMethod', 'bank_transfer')"
                                class="flex flex-col items-center p-3 rounded-lg border-2 transition-all {{ $paymentMethod === 'bank_transfer' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300' }}">
                                <x-heroicon-o-building-library class="w-6 h-6 {{ $paymentMethod === 'bank_transfer' ? 'text-primary-600' : 'text-gray-500' }}" />
                                <span class="text-xs mt-1 {{ $paymentMethod === 'bank_transfer' ? 'text-primary-600 font-medium' : 'text-gray-600 dark:text-gray-400' }}">{{ __('Bank') }}</span>
                            </button>
                            <button type="button" wire:click="$set('paymentMethod', 'mobile_payment')"
                                class="flex flex-col items-center p-3 rounded-lg border-2 transition-all {{ $paymentMethod === 'mobile_payment' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300' }}">
                                <x-heroicon-o-device-phone-mobile class="w-6 h-6 {{ $paymentMethod === 'mobile_payment' ? 'text-primary-600' : 'text-gray-500' }}" />
                                <span class="text-xs mt-1 {{ $paymentMethod === 'mobile_payment' ? 'text-primary-600 font-medium' : 'text-gray-600 dark:text-gray-400' }}">{{ __('Mobile') }}</span>
                            </button>
                            <button type="button" wire:click="$set('paymentMethod', 'credit')"
                                class="flex flex-col items-center p-3 rounded-lg border-2 transition-all {{ $paymentMethod === 'credit' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300' }}">
                                <x-heroicon-o-clock class="w-6 h-6 {{ $paymentMethod === 'credit' ? 'text-primary-600' : 'text-gray-500' }}" />
                                <span class="text-xs mt-1 {{ $paymentMethod === 'credit' ? 'text-primary-600 font-medium' : 'text-gray-600 dark:text-gray-400' }}">{{ __('Credit') }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- Payment Amount --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Payment Amount') }}
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input type="number" wire:model.live="paymentAmount"
                                step="{{ $decimalPlaces > 0 ? '0.' . str_repeat('0', $decimalPlaces - 1) . '1' : '1' }}"
                                min="0"
                                class="w-full text-lg" />
                            <x-slot name="suffix">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $currencySymbol }}</span>
                            </x-slot>
                        </x-filament::input.wrapper>
                    </div>

                    {{-- Change Calculation (for cash) --}}
                    @if ($paymentMethod === 'cash' && $paymentAmount > $totalAmount)
                        <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-green-700 dark:text-green-300">{{ __('Change') }}</span>
                                <span class="text-lg font-bold text-green-700 dark:text-green-300">
                                    {{ number_format($paymentAmount - $totalAmount, $decimalPlaces) }} {{ $currencySymbol }}
                                </span>
                            </div>
                        </div>
                    @endif

                    {{-- Quick Amount Buttons (for cash) --}}
                    @if ($paymentMethod === 'cash')
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('Quick Amount') }}</label>
                            <div class="grid grid-cols-4 gap-2">
                                @php
                                    $quickAmounts = [1000, 5000, 10000, 25000, 50000, 100000];
                                @endphp
                                @foreach ($quickAmounts as $amount)
                                    <button type="button" wire:click="$set('paymentAmount', {{ $amount }})"
                                        class="px-2 py-1.5 text-xs bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors">
                                        {{ number_format($amount, 0) }}
                                    </button>
                                @endforeach
                                <button type="button" wire:click="$set('paymentAmount', {{ $totalAmount }})"
                                    class="px-2 py-1.5 text-xs bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 hover:bg-primary-200 dark:hover:bg-primary-900/50 rounded transition-colors col-span-2">
                                    {{ __('Exact') }}
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                {{-- Split Payment Mode --}}
                <div class="space-y-4">
                    {{-- Split Payment Items --}}
                    <div class="space-y-2">
                        @forelse ($splitPayments as $index => $payment)
                            <div class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <select wire:model.live="splitPayments.{{ $index }}.method"
                                    class="flex-shrink-0 w-32 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-lg">
                                    <option value="cash">{{ __('Cash') }}</option>
                                    <option value="card">{{ __('Card') }}</option>
                                    <option value="bank_transfer">{{ __('Bank') }}</option>
                                    <option value="mobile_payment">{{ __('Mobile') }}</option>
                                    <option value="credit">{{ __('Credit') }}</option>
                                </select>
                                <input type="number" wire:model.live="splitPayments.{{ $index }}.amount"
                                    step="{{ $decimalPlaces > 0 ? '0.' . str_repeat('0', $decimalPlaces - 1) . '1' : '1' }}"
                                    min="0" placeholder="0"
                                    class="flex-1 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-lg" />
                                <span class="text-xs text-gray-500">{{ $currencySymbol }}</span>
                                <button type="button" wire:click="removeSplitPayment({{ $index }})"
                                    class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </div>
                        @empty
                            <div class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
                                {{ __('No payment methods added. Click "Add Payment" below.') }}
                            </div>
                        @endforelse
                    </div>

                    {{-- Add Payment Button --}}
                    <button type="button" wire:click="addSplitPayment"
                        class="w-full flex items-center justify-center gap-2 p-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-400 hover:border-primary-500 hover:text-primary-600 transition-colors">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        <span class="text-sm">{{ __('Add Payment Method') }}</span>
                    </button>

                    {{-- Split Payment Summary --}}
                    @php
                        $splitTotal = collect($splitPayments)->sum('amount');
                        $remaining = $totalAmount - $splitTotal;
                    @endphp
                    <div class="p-3 rounded-lg {{ $remaining <= 0 ? 'bg-green-50 dark:bg-green-900/20' : 'bg-yellow-50 dark:bg-yellow-900/20' }}">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="{{ $remaining <= 0 ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }}">{{ __('Total Paid') }}</span>
                            <span class="font-medium {{ $remaining <= 0 ? 'text-green-700 dark:text-green-300' : 'text-yellow-700 dark:text-yellow-300' }}">
                                {{ number_format($splitTotal, $decimalPlaces) }} {{ $currencySymbol }}
                            </span>
                        </div>
                        @if ($remaining > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-yellow-600 dark:text-yellow-400">{{ __('Remaining') }}</span>
                                <span class="font-bold text-yellow-700 dark:text-yellow-300">
                                    {{ number_format($remaining, $decimalPlaces) }} {{ $currencySymbol }}
                                </span>
                            </div>
                        @elseif ($remaining < 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-green-600 dark:text-green-400">{{ __('Change') }}</span>
                                <span class="font-bold text-green-700 dark:text-green-300">
                                    {{ number_format(abs($remaining), $decimalPlaces) }} {{ $currencySymbol }}
                                </span>
                            </div>
                        @else
                            <div class="text-center text-green-600 dark:text-green-400 text-sm font-medium">
                                {{ __('Payment complete!') }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <x-slot name="footerActions">
            <x-filament::button wire:click="closePaymentModal" color="gray">
                {{ __('Cancel') }}
            </x-filament::button>
            <x-filament::button
                wire:click="createInvoiceAndPayment"
                color="primary"
                :disabled="$useSplitPayment && collect($splitPayments)->sum('amount') < ($formData['total_amount'] ?? 0)">
                {{ __('Process Payment') }}
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- Sale Success Modal --}}
    @if ($showSuccessModal && $lastSale)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" wire:key="success-modal">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
                        <x-heroicon-o-check-circle class="w-10 h-10 text-white" />
                    </div>
                    <h3 class="text-xl font-bold text-white">{{ __('Sale Completed!') }}</h3>
                    <p class="text-green-100 text-sm mt-1">{{ __('Invoice') }} #{{ $lastSale->sale_number }}</p>
                </div>

                {{-- Content --}}
                <div class="p-6 space-y-4">
                    @php
                        $currency = $selectedCurrency
                            ? $currencies->find($selectedCurrency)
                            : $currencies->where('base', true)->first();
                        $currencySymbol = $currency->symbol ?? 'IQD';
                        $decimalPlaces = $currency->decimal_places ?? 0;
                    @endphp

                    {{-- Sale Summary --}}
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">{{ __('Total Amount') }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ number_format($lastSale->total_amount, $decimalPlaces) }} {{ $currencySymbol }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">{{ __('Paid Amount') }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ number_format($lastSale->paid_amount, $decimalPlaces) }} {{ $currencySymbol }}
                            </span>
                        </div>
                        @if ($changeAmount > 0)
                            <div class="flex justify-between text-sm border-t border-gray-200 dark:border-gray-600 pt-2 mt-2">
                                <span class="text-green-600 dark:text-green-400 font-medium">{{ __('Change') }}</span>
                                <span class="font-bold text-green-600 dark:text-green-400 text-lg">
                                    {{ number_format($changeAmount, $decimalPlaces) }} {{ $currencySymbol }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Print Options --}}
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" wire:click="printThermalReceipt"
                            class="flex flex-col items-center p-4 border-2 border-gray-200 dark:border-gray-600 rounded-lg hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                            <x-heroicon-o-receipt-percent class="w-8 h-8 text-gray-600 dark:text-gray-400 mb-2" />
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Print Receipt') }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('80mm Thermal') }}</span>
                        </button>
                        <button type="button" wire:click="viewInvoice"
                            class="flex flex-col items-center p-4 border-2 border-gray-200 dark:border-gray-600 rounded-lg hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                            <x-heroicon-o-document-text class="w-8 h-8 text-gray-600 dark:text-gray-400 mb-2" />
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('View Invoice') }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('A4 Format') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 flex gap-3">
                    <x-filament::button wire:click="newSale" color="primary" class="flex-1">
                        <div class="flex items-center justify-center gap-2">
                            <x-heroicon-o-plus class="w-4 h-4" />
                            {{ __('New Sale') }}
                        </div>
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

    <script>
        function posFilter() {
            return {
                searchQuery: '',
                selectedCategory: '',

                init() {
                    this.searchQuery = '';
                    this.selectedCategory = '';
                },

                filterProduct(productId, productName, categoryId) {
                    const matchesSearch = this.searchQuery === '' ||
                        productName.toLowerCase().includes(this.searchQuery.toLowerCase());
                    const matchesCategory = this.selectedCategory === '' ||
                        categoryId == this.selectedCategory;

                    return matchesSearch && matchesCategory;
                },

                hasVisibleProducts() {
                    const products = document.querySelectorAll('[x-show]');
                    let visibleCount = 0;

                    products.forEach(product => {
                        if (product.style.display !== 'none') {
                            visibleCount++;
                        }
                    });

                    return visibleCount > 0;
                },

                clearFilters() {
                    this.searchQuery = '';
                    this.selectedCategory = '';
                }
            }
        }

        // Thermal Receipt Print Function - opens in new tab with preview
        window.printThermalReceipt = function(html) {
            // Translations (defined outside template literal to avoid Blade parsing issues)
            const translations = {
                popupAlert: @json(__('Please allow popups for printing receipts')),
                receiptTitle: @json(__('Receipt') . ' - ' . config('app.name')),
                printReceipt: @json(__('Print Receipt')),
                close: @json(__('Close'))
            };
            const direction = @json(app()->getLocale() === 'ar' || app()->getLocale() === 'ckb' ? 'rtl' : 'ltr');

            // Open in a new tab
            const printTab = window.open('', '_blank');

            if (!printTab) {
                alert(translations.popupAlert);
                return;
            }

            const receiptHtml = `
                <!DOCTYPE html>
                <html dir="${direction}">
                <head>
                    <meta charset="UTF-8">
                    <title>${translations.receiptTitle}</title>
                    <style>
                        @page {
                            size: 80mm auto;
                            margin: 0;
                        }
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }
                        html {
                            background: #f3f4f6;
                        }
                        body {
                            font-family: 'Courier New', 'Lucida Console', monospace;
                            font-size: 12px;
                            width: 80mm;
                            max-width: 80mm;
                            margin: 20px auto;
                            padding: 5mm;
                            background: white;
                            color: black;
                            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                            border-radius: 4px;
                        }
                        .receipt {
                            width: 100% !important;
                            max-width: 74mm !important;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        td, th {
                            padding: 2px 0;
                            vertical-align: top;
                        }
                        hr {
                            border: none;
                            border-top: 1px dashed #000;
                            margin: 8px 0;
                        }
                        h2 {
                            font-size: 16px;
                            margin-bottom: 5px;
                            text-align: center;
                        }
                        .actions {
                            width: 80mm;
                            max-width: 80mm;
                            margin: 0 auto 20px;
                            display: flex;
                            gap: 10px;
                        }
                        .print-btn {
                            flex: 1;
                            padding: 12px 20px;
                            background: #4F46E5;
                            color: white;
                            border: none;
                            border-radius: 8px;
                            cursor: pointer;
                            font-size: 14px;
                            font-weight: 500;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                        }
                        .print-btn:hover {
                            background: #4338CA;
                        }
                        .close-btn {
                            padding: 12px 20px;
                            background: #6B7280;
                            color: white;
                            border: none;
                            border-radius: 8px;
                            cursor: pointer;
                            font-size: 14px;
                            font-weight: 500;
                        }
                        .close-btn:hover {
                            background: #4B5563;
                        }
                        @media print {
                            html {
                                background: white;
                            }
                            body {
                                width: 80mm;
                                max-width: 80mm;
                                margin: 0;
                                box-shadow: none;
                                border-radius: 0;
                            }
                            .no-print {
                                display: none !important;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="no-print actions">
                        <button class="print-btn" onclick="window.print()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                            ${translations.printReceipt}
                        </button>
                        <button class="close-btn" onclick="window.close()">${translations.close}</button>
                    </div>
                    ${html}
                </body>
                </html>
            `;

            printTab.document.write(receiptHtml);
            printTab.document.close();
        };
    </script>
    @endif
</x-filament-panels::page>
