<?php

namespace Database\Factories;

use App\Models\Value;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Value>
 */
class ValueFactory extends Factory
{
    protected $model = Value::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['الصدق', 'الأمانة', 'الصبر', 'العدل', 'الإحسان', 'الكرم', 'التواضع', 'التعاون']),
            'description' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 100),
        ];
    }
}
