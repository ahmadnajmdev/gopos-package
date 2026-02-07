<div>
    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
        {{ __('Business Settings') }}
    </h2>
    <p class="text-gray-600 dark:text-gray-400 mb-6">
        {{ __('Configure your business information. These settings will appear on invoices and receipts.') }}
    </p>

    <div class="space-y-5">
        {{-- Business Name --}}
        <div>
            <label for="businessName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ __('Business Name') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <input type="text"
                       id="businessName"
                       wire:model.blur="businessName"
                       class="w-full ps-10 pe-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                       placeholder="{{ __('Your Business Name') }}">
            </div>
            @error('businessName')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Currency --}}
        <div>
            <label for="currencyId" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ __('Primary Currency') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <select id="currencyId"
                        wire:model.live="currencyId"
                        class="w-full ps-10 pe-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition appearance-none">
                    <option value="">{{ __('Select currency') }}</option>
                    @foreach($this->currencies as $currency)
                        <option value="{{ $currency['id'] }}">{{ $currency['name'] }} ({{ $currency['symbol'] }})</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 end-0 pe-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>
            @error('currencyId')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Business Address --}}
        <div>
            <label for="businessAddress" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ __('Business Address') }}
            </label>
            <div class="relative">
                <div class="absolute top-3 start-0 ps-3 flex items-start pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <textarea id="businessAddress"
                          wire:model.blur="businessAddress"
                          rows="2"
                          class="w-full ps-10 pe-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition resize-none"
                          placeholder="{{ __('Street, City, Country') }}"></textarea>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- Phone --}}
            <div>
                <label for="businessPhone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('Phone Number') }}
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                    </div>
                    <input type="tel"
                           id="businessPhone"
                           wire:model.blur="businessPhone"
                           class="w-full ps-10 pe-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="+964 xxx xxx xxxx">
                </div>
            </div>

            {{-- Email --}}
            <div>
                <label for="businessEmail" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('Business Email') }}
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <input type="email"
                           id="businessEmail"
                           wire:model.blur="businessEmail"
                           class="w-full ps-10 pe-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="contact@yourbusiness.com">
                </div>
            </div>
        </div>

        {{-- Demo Data Option --}}
        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
            <label class="flex items-start gap-4 cursor-pointer">
                <div class="flex-shrink-0 mt-0.5">
                    <input type="checkbox"
                           wire:model.live="seedDemoData"
                           class="w-5 h-5 rounded border-blue-300 text-purple-600 focus:ring-purple-500 transition">
                </div>
                <div>
                    <span class="font-medium text-blue-800 dark:text-blue-200">
                        {{ __('Install demo data') }}
                    </span>
                    <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                        {{ __('Add sample products, customers, and transactions to help you explore the system. Recommended for testing.') }}
                    </p>
                </div>
            </label>
        </div>
    </div>
</div>
