<?php

namespace Gopos\Database\Factories;

use Gopos\Models\Employee;
use Gopos\Models\LeaveBalance;
use Gopos\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\LeaveBalance>
 */
class LeaveBalanceFactory extends Factory
{
    protected $model = LeaveBalance::class;

    public function definition(): array
    {
        $entitled = $this->faker->numberBetween(15, 30);
        $used = $this->faker->numberBetween(0, $entitled);

        return [
            'employee_id' => Employee::factory(),
            'leave_type_id' => LeaveType::factory(),
            'year' => date('Y'),
            'entitled_days' => $entitled,
            'used_days' => $used,
            'pending_days' => 0,
            'carried_forward' => 0,
            'adjustment' => 0,
        ];
    }
}
