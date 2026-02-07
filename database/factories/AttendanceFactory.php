<?php

namespace Gopos\Database\Factories;

use Gopos\Models\Attendance;
use Gopos\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('-1 month', 'now');
        $checkIn = $date->setTime($this->faker->numberBetween(7, 9), $this->faker->numberBetween(0, 59));
        $checkOut = (clone $checkIn)->modify('+8 hours');

        return [
            'employee_id' => Employee::factory(),
            'date' => $date->format('Y-m-d'),
            'check_in' => $checkIn->format('H:i:s'),
            'check_out' => $checkOut->format('H:i:s'),
            'status' => 'present',
            'worked_hours' => 8,
        ];
    }

    public function present(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'present',
        ]);
    }

    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'absent',
            'check_in' => null,
            'check_out' => null,
            'worked_hours' => 0,
        ]);
    }

    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'late',
            'check_in' => '09:30:00',
        ]);
    }
}
