<?php

namespace Gopos\Database\Factories;

use Gopos\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\LeaveType>
 */
class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Annual Leave', 'Sick Leave', 'Emergency Leave',
                'Maternity Leave', 'Paternity Leave', 'Unpaid Leave',
            ]),
            'name_ar' => null,
            'name_ckb' => null,
            'code' => strtoupper($this->faker->unique()->bothify('LT-###')),
            'default_days_per_year' => $this->faker->numberBetween(5, 30),
            'is_paid' => $this->faker->boolean(80),
            'requires_approval' => true,
            'requires_document' => false,
            'can_carry_forward' => false,
            'max_carry_forward_days' => 0,
            'is_active' => true,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_paid' => true,
        ]);
    }

    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_paid' => false,
        ]);
    }
}
