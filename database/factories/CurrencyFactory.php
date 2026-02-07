<?php

namespace Gopos\Database\Factories;

use Gopos\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\Currency>
 */
class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['US Dollar', 'Euro', 'British Pound', 'Iraqi Dinar']),
            'symbol' => $this->faker->randomElement(['$', '€', '£', 'IQD']),
            'code' => $this->faker->unique()->randomElement(['USD', 'EUR', 'GBP', 'IQD']),
            'exchange_rate' => 1,
            'decimal_places' => 2,
            'base' => false,
        ];
    }

    public function base(): static
    {
        return $this->state(fn (array $attributes) => [
            'base' => true,
            'exchange_rate' => 1,
        ]);
    }
}
