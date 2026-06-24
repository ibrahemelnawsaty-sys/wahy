<?php

namespace Database\Factories;

use App\Models\Concept;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Lesson>
 */
class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition(): array
    {
        return [
            'concept_id' => Concept::factory(),
            'title'      => 'درس ' . fake()->words(3, true),
            'content'    => fake()->paragraph(),
            'type'       => 'text',
            'duration'   => fake()->numberBetween(5, 30),
            'points'     => 10,
            'order'      => fake()->numberBetween(1, 50),
            'status'     => 'active',
        ];
    }
}
