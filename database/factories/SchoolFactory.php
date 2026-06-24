<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<School>
 */
class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        return [
            'name'         => 'مدرسة ' . fake()->words(2, true),
            'address'      => fake()->streetAddress(),
            'city'         => fake()->city(),
            'country'      => 'Saudi Arabia',
            'contact_email' => fake()->unique()->safeEmail(),
            'contact_phone' => '+9665' . fake()->numerify('########'),
            'status'       => 'active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
