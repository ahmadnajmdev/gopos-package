<?php

namespace Gopos\Database\Factories;

use Gopos\Enums\LeaveStatus;
use Gopos\Models\Employee;
use Gopos\Models\Leave;
use Gopos\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\Leave>
 */
class LeaveFactory extends Factory
{
    protected $model = Leave::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $days = $this->faker->numberBetween(1, 5);
        $endDate = (clone $startDate)->modify("+{$days} days");

        return [
            'branch_id' => \Gopos\Models\Branch::factory(),
            'employee_id' => Employee::factory(),
            'leave_type_id' => LeaveType::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days' => $days,
            'status' => LeaveStatus::Pending,
            'reason' => $this->faker->sentence(),
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => LeaveStatus::Approved]);
    }

    public function rejected(): static
    {
        return $this->state(['status' => LeaveStatus::Rejected]);
    }
}
