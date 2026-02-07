<?php

namespace Gopos\Database\Factories;

use Gopos\Models\TaxCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\TaxCode>
 */
class TaxCodeFactory extends Factory
{
    protected $model = TaxCode::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true).' Tax',
            'name_ar' => null,
            'code' => strtoupper($this->faker->unique()->bothify('TAX-###')),
            'rate' => $this->faker->randomElement([0.05, 0.10, 0.15, 0.20]),
            'type' => $this->faker->randomElement(['inclusive', 'exclusive']),
            'is_active' => true,
            'description' => $this->faker->sentence(),
        ];
    }

    public function exclusive(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'exclusive',
        ]);
    }

    public function inclusive(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'inclusive',
        ]);
    }

    public function zeroRate(): static
    {
        return $this->state(fn (array $attributes) => [
            'rate' => 0,
            'name' => 'Zero Rate Tax',
        ]);
    }
}
