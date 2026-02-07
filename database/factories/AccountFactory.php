<?php

namespace Gopos\Database\Factories;

use Gopos\Models\Account;
use Gopos\Models\AccountType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'account_type_id' => AccountType::first()?->id ?? AccountType::factory(),
            'code' => $this->faker->unique()->numerify('####'),
            'name' => $this->faker->words(3, true),
            'name_ar' => null,
            'is_active' => true,
            'is_system' => false,
            'opening_balance' => 0,
            'current_balance' => 0,
            'description' => $this->faker->sentence(),
        ];
    }

    public function asset(): static
    {
        return $this->state(function (array $attributes) {
            $type = AccountType::where('name', 'Asset')->first()
                ?? AccountType::create(['name' => 'Asset', 'normal_balance' => 'debit', 'display_order' => 1]);

            return ['account_type_id' => $type->id];
        });
    }

    public function liability(): static
    {
        return $this->state(function (array $attributes) {
            $type = AccountType::where('name', 'Liability')->first()
                ?? AccountType::create(['name' => 'Liability', 'normal_balance' => 'credit', 'display_order' => 2]);

            return ['account_type_id' => $type->id];
        });
    }

    public function revenue(): static
    {
        return $this->state(function (array $attributes) {
            $type = AccountType::where('name', 'Revenue')->first()
                ?? AccountType::create(['name' => 'Revenue', 'normal_balance' => 'credit', 'display_order' => 4]);

            return ['account_type_id' => $type->id];
        });
    }

    public function expense(): static
    {
        return $this->state(function (array $attributes) {
            $type = AccountType::where('name', 'Expense')->first()
                ?? AccountType::create(['name' => 'Expense', 'normal_balance' => 'debit', 'display_order' => 5]);

            return ['account_type_id' => $type->id];
        });
    }

    public function equity(): static
    {
        return $this->state(function (array $attributes) {
            $type = AccountType::where('name', 'Equity')->first()
                ?? AccountType::create(['name' => 'Equity', 'normal_balance' => 'credit', 'display_order' => 3]);

            return ['account_type_id' => $type->id];
        });
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
        ]);
    }

    public function withCode(string $code): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => $code,
        ]);
    }
}
