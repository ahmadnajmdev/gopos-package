<?php

namespace Gopos\Database\Factories;

use Gopos\Models\PayrollComponent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\PayrollComponent>
 */
class PayrollComponentFactory extends Factory
{
    protected $model = PayrollComponent::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Housing Allowance', 'Transport Allowance', 'Food Allowance',
                'Phone Allowance', 'Internet Allowance', 'Health Insurance',
                'Social Security', 'Tax Deduction',
            ]),
            'name_ar' => null,
            'name_ckb' => null,
            'code' => strtoupper($this->faker->unique()->bothify('SC-###')),
            'type' => $this->faker->randomElement(['earning', 'deduction']),
            'calculation_type' => $this->faker->randomElement(['fixed', 'percentage']),
            'default_amount' => $this->faker->randomFloat(2, 50, 500),
            'is_taxable' => $this->faker->boolean(70),
            'is_active' => true,
        ];
    }

    public function earning(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'earning',
        ]);
    }

    public function deduction(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'deduction',
        ]);
    }

    public function fixed(): static
    {
        return $this->state(fn (array $attributes) => [
            'calculation_type' => 'fixed',
        ]);
    }

    public function percentage(): static
    {
        return $this->state(fn (array $attributes) => [
            'calculation_type' => 'percentage',
        ]);
    }
}
