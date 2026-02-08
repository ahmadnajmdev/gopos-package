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
            'name' => $this->faker->unique()->randomElement(['Annual Leave', 'Sick Leave', 'Maternity Leave', 'Personal Leave', 'Unpaid Leave']),
            'days_allowed' => $this->faker->numberBetween(5, 30),
            'is_paid' => true,
            'is_active' => true,
        ];
    }

    public function unpaid(): static
    {
        return $this->state(['is_paid' => false]);
    }
}
