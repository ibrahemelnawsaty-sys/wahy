<?php

namespace Database\Factories;

use App\Models\Concept;
use App\Models\Value;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Concept>
 */
class ConceptFactory extends Factory
{
    protected $model = Concept::class;

    public function definition(): array
    {
        return [
            'value_id'    => Value::factory(),
            'name'        => fake()->words(2, true),
            'description' => fake()->sentence(),
            'order'       => fake()->numberBetween(1, 50),
        ];
    }
}
