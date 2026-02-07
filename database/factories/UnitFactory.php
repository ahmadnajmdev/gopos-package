<?php

namespace Gopos\Database\Factories;

use Gopos\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\Unit>
 */
class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Piece', 'Box', 'Pack', 'Kilogram', 'Gram',
                'Liter', 'Meter', 'Dozen', 'Set', 'Unit',
            ]),
            'abbreviation' => $this->faker->unique()->randomElement([
                'pcs', 'box', 'pack', 'kg', 'g', 'L', 'm', 'dz', 'set', 'unit',
            ]),
        ];
    }
}
