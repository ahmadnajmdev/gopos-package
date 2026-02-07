<?php

namespace Gopos\Database\Seeders;

use Gopos\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'name' => 'Iraqi Dinar',
                'code' => 'IQD',
                'symbol' => 'د.ع',
                'exchange_rate' => 1.0000,
                'decimal_places' => 0,
                'base' => true,
            ],
            [
                'name' => 'US Dollar',
                'code' => 'USD',
                'symbol' => '$',
                'exchange_rate' => 0.0007, // Approximate rate: 1 USD = ~1450 IQD
                'decimal_places' => 2,
                'base' => false,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
