<div>
    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
        {{ __('Administrator Account') }}
    </h2>
    <p class="text-gray-600 dark:text-gray-400 mb-6">
        {{ __('Create your administrator account. This will be the super admin with full access to all features.') }}
    </p>

    <div class="space-y-5">
        {{-- Full Name --}}
        <div>
            <label for="adminName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ __('Full Name') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <input type="text"
                       id="adminName"
                       wire:model.blur="adminName"
                       class="w-full ps-10 pe-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                       placeholder="{{ __('Enter your full name') }}">
            </div>
            @error('adminName')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="adminEmail" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ __('Email Address') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <input type="email"
                       id="adminEmail"
                       wire:model.blur="adminEmail"
                       class="w-full ps-10 pe-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                       placeholder="{{ __('admin@example.com') }}">
            </div>
            @error('adminEmail')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="adminPassword" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ __('Password') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <input type="password"
                       id="adminPassword"
                       wire:model.blur="adminPassword"
                       class="w-full ps-10 pe-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                       placeholder="{{ __('Minimum 8 characters') }}">
            </div>
            @error('adminPassword')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="adminPasswordConfirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ __('Confirm Password') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <input type="password"
                       id="adminPasswordConfirmation"
                       wire:model.blur="adminPasswordConfirmation"
                       class="w-full ps-10 pe-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                       placeholder="{{ __('Re-enter your password') }}">
            </div>
            @error('adminPasswordConfirmation')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Security Note --}}
    <div class="mt-6 flex items-start gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
        <svg class="w-5 h-5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <div class="text-sm text-amber-800 dark:text-amber-200">
            <p class="font-medium mb-1">{{ __('Security Tip') }}</p>
            <p>{{ __('Use a strong password with a mix of letters, numbers, and symbols. This account will have full access to all system features.') }}</p>
        </div>
    </div>
</div>
