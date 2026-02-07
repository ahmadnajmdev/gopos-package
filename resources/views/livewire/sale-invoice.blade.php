@php
    // Determine text direction based on locale
    $dir = App::isLocale('ckb') ? 'rtl' : 'ltr';
    // Define direction-aware helper variables for Tailwind classes
    $textAlignStart = $dir === 'rtl' ? 'start' : 'start';
    $textAlignEnd = $dir === 'rtl' ? 'start' : 'start';
    $paddingStart = $dir === 'rtl' ? 'pr' : 'pl';
    $paddingEnd = $dir === 'rtl' ? 'pl' : 'pr';
@endphp
@vite('resources/css/app.css')
{{--
    NOTE FOR IMPLEMENTATION:
    1. This code assumes Tailwind CSS and the custom font (@font-face) are loaded globally in the parent page/layout.
    2. If this needs to be completely self-contained (e.g., for rendering standalone PDF),
       you might need to include <script src="https://cdn.tailwindcss.com"></script>
       and the <style> block with @font-face inside this div, although it's not standard HTML structure.
    3. Ensure the custom font file ('YourCustomFont.ttf') is accessible via a public path.
    4. Replace placeholder company details and logo with actual data.
--}}

{{-- Apply custom font via a class (assuming 'font-custom' is defined globally or via Tailwind config) --}}
<div dir="{{ $dir }}" class="font-custom max-w-4xl p-8 mx-auto my-8 bg-white rounded-lg "
    style="font-family: 'YourCustomFont', sans-serif; /* Direct style as fallback */">

    @php
        $invoiceCurrency = $sale->currency ?? \Gopos\Models\Currency::getBaseCurrency();
        $currencySymbol = $invoiceCurrency?->symbol ?? 'IQD';
        $currencyCode = $invoiceCurrency?->code ?? 'IQD';
        $decimalPlaces = $invoiceCurrency?->decimal_places ?? 0;
    @endphp

    <style>
        @font-face {
            font-family: 'Rabar';
            src: url({{ asset('css/fonts/Rabar_021.ttf') }}) format('truetype');
            /* Adjust path */
        }

        * {
            /* Apply globally if needed */
            font-family: 'Rabar', sans-serif;

        }
    </style>


    {{-- Header --}}
    <div class="flex items-center justify-between pb-6 mb-6 border-b border-gray-200">
        <div class="text-{{ $textAlignStart }}">
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Invoice') }}</h1>
            <p class="text-sm text-gray-500">{{ __('Invoice') }} #{{ $sale->sale_number }}</p>
            <p class="text-sm text-gray-500">{{ __('Date') }}: {{ $sale->sale_date }}</p>
        </div>
        <div class="text-{{ $textAlignEnd }}">
            {{-- Company Logo --}}
            @if(setting('invoice.invoice_logo') || setting('general.logo'))
                <img src="{{ Storage::url(setting('invoice.invoice_logo') ?? setting('general.logo')) }}" alt="Company Logo" class="h-16">
            @endif
        </div>
    </div>

    {{-- Billing Information --}}
    <div class="flex justify-between mb-8">
        <div class="w-1/2 {{ $paddingEnd }}-4 text-{{ $textAlignStart }}">
            <h2 class="mb-2 font-semibold text-gray-700">{{ __('Billed From') }}</h2>
            <p class="text-sm font-medium text-gray-700">{{ setting('invoice.your_company_name') ?? config('app.name') }}</p>
            @if(setting('invoice.your_company_address'))
                <p class="text-sm text-gray-600">{{ setting('invoice.your_company_address') }}</p>
            @endif
            @if (setting('invoice.your_company_phone'))
                <p class="text-sm text-gray-600">{{ __('Phone') }}: {{ setting('invoice.your_company_phone') }}</p>
            @endif
            @if (setting('invoice.your_company_email'))
                <p class="text-sm text-gray-600">{{ __('Email') }}: {{ setting('invoice.your_company_email') }}</p>
            @endif
        </div>
        <div class="w-1/2 {{ $paddingStart }}-4 text-{{ $textAlignEnd }}">
            <h2 class="mb-2 font-semibold text-gray-700">
                {{ __('Billed To') }}
            </h2>
            <p class="text-sm text-gray-600">{{ __('Name') . ':' . ($sale->customer_name ?? __('Walk-in Customer')) }}</p>
            @if ($sale->customer)
                <p class="text-sm text-gray-600">{{ __('Address') . ':' . $sale->customer->address }}</p>
                @if ($sale->customer->phone)
                    <p class="text-sm text-gray-600">{{ __('Phone') }}: {{ $sale->customer->phone }}</p>
                @endif
                @if ($sale->customer->email)
                    <p class="text-sm text-gray-600">{{ __('Email') }}: {{ $sale->customer->email }}</p>
                @endif
            @else
                <p class="text-sm text-gray-500 italic">{{ __('Walk-in Customer - No contact details available') }}</p>
            @endif
        </div>
    </div>

    {{-- Invoice Items --}}
    <div class="w-full mb-8 overflow-hidden border border-gray-200 rounded-lg">
        <table class="w-full text-sm">
            <thead class="border-b-2 border-gray-200 bg-gray-50">
                <tr>
                    <th
                        class="px-4 py-3 font-semibold tracking-wider text-gray-600 uppercase text-{{ $textAlignStart }}">
                        {{ __('Name') }}</th>
                    <th
                        class="px-4 py-3 font-semibold tracking-wider text-gray-600 uppercase text-{{ $textAlignStart }}">
                        {{ __('Quantity') }}</th>
                    <th
                        class="px-4 py-3 font-semibold tracking-wider text-gray-600 uppercase text-{{ $textAlignStart }}">
                        {{ __('Unit price') }}</th>
                    <th
                        class="px-4 py-3 font-semibold tracking-wider text-gray-600 uppercase text-{{ $textAlignStart }}">
                        {{ __('Total amount') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($sale->items as $item)
                    <tr>
                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap text-{{ $textAlignStart }}">
                            {{ $item->product?->name ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap text-{{ $textAlignStart }}">
                            {{ Number::format($item->stock, locale: 'en') . ' ' . $item->product?->unit?->abbreviation }}</td>
                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap text-{{ $textAlignStart }}">
                            {{ Number::format($item->price, $decimalPlaces, locale: 'en') }} {{ $currencySymbol }}</td>
                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap text-{{ $textAlignStart }}">
                            {{ Number::format($item->stock * $item->price, $decimalPlaces, locale: 'en') }} {{ $currencySymbol }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-gray-500">No items in this sale.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <div class="flex justify-end mb-8 text-sm text-gray-700">
        <div class="w-full max-w-xs text-{{ $textAlignEnd }}">
            <div class="flex justify-between py-1">
                <span>{{ __('Sub total') }}</span>
                <span>{{ Number::format($sale->sub_total, $decimalPlaces, locale: 'en') }} {{ $currencySymbol }}</span>
            </div>
            <div class="flex justify-between py-1">
                <span>{{ __('Discount amount') }}</span>
                <span>-{{ Number::format($sale->discount, $decimalPlaces, locale: 'en') }} {{ $currencySymbol }}</span>
            </div>
            {{-- Optional Tax --}}
            {{-- <div class="flex justify-between py-1">
                <span>Tax (10%)</span>
                <span>{{ number_format(($sale->sub_total - $sale->discount_amount) * 0.10, 2) }} {{ $currencySymbol }}</span>
            </div> --}}
            <div class="flex justify-between py-2 mt-2 font-semibold border-t-2 border-gray-200">
                <span>{{ __('Total amount') }}</span>
                <span>{{ Number::format($sale->total_amount, $decimalPlaces, locale: 'en') }}
                    {{ $currencySymbol }}</span>
            </div>
            {{-- Optional Paid/Due --}}
            @if (isset($sale->paid_amount))
                <div class="flex justify-between py-1 mt-2 text-green-600">
                    <span>{{ __('Paid amount') }}</span>
                    <span>{{ Number::format($sale->paid_amount, $decimalPlaces, locale: 'en') }} {{ $currencySymbol }}</span>
                </div>
            @endif
            @if (isset($sale->amount_due))
                <div
                    class="flex justify-between py-1 font-medium {{ $sale->amount_due > 0 ? 'text-red-600' : 'text-gray-700' }}">
                    <span>{{ __('Due Amount') }}</span>
                    <span>{{ Number::format($sale->amount_due, $decimalPlaces, locale: 'en') }} {{ $currencySymbol }}</span>
                </div>
            @endif
        </div>
    </div>
    {{-- Footer Notes --}}
    <div class="pt-6 mt-8 text-xs text-center text-gray-500 border-t border-gray-200">
        <p>{{ setting('invoice.invoice_footer_title') }}</p>
        <p>{{ setting('invoice.invoice_footer_description') }}</p>
    </div>


    @if (request()->routeIs('print-sale-invoice'))
    <script>
        window.onload = function() {
            window.print();

            window.onafterprint = function() {
                window.close();
            };
        };
    </script>
    @endif
</div>
