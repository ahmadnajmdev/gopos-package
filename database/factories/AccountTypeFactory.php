<?php

namespace Gopos\Database\Factories;

use Gopos\Models\AccountType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\AccountType>
 */
class AccountTypeFactory extends Factory
{
    protected $model = AccountType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Asset', 'Liability', 'Equity', 'Revenue', 'Expense']),
            'name_ar' => null,
            'normal_balance' => 'debit',
            'display_order' => $this->faker->numberBetween(1, 10),
        ];
    }

    public function asset(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Asset',
            'normal_balance' => 'debit',
            'display_order' => 1,
        ]);
    }

    public function liability(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Liability',
            'normal_balance' => 'credit',
            'display_order' => 2,
        ]);
    }

    public function equity(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Equity',
            'normal_balance' => 'credit',
            'display_order' => 3,
        ]);
    }

    public function revenue(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Revenue',
            'normal_balance' => 'credit',
            'display_order' => 4,
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Expense',
            'normal_balance' => 'debit',
            'display_order' => 5,
        ]);
    }
}
