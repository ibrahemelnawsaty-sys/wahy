<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    protected $model = Classroom::class;

    public function definition(): array
    {
        return [
            'school_id'     => School::factory(),
            'teacher_id'    => null,
            'name'          => 'الصف ' . fake()->randomElement(['الأول', 'الثاني', 'الثالث', 'الرابع']) . ' ' . fake()->randomLetter(),
            'grade_level'   => fake()->randomElement(['ابتدائي', 'متوسط', 'ثانوي']),
            'academic_year' => '2025-2026',
            'capacity'      => fake()->numberBetween(20, 35),
            'status'        => 'active',
        ];
    }
}
