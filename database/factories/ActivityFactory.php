<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'created_by' => null, // اختياري — المُنشِئ
            'classroom_id' => null,
            'title' => 'نشاط ' . fake()->words(3, true),
            'description' => fake()->sentence(),
            'type' => 'quiz',
            'questions' => [
                ['question' => 'سؤال تجريبي 1', 'options' => ['أ', 'ب', 'ج', 'د'], 'correct_answer' => 0],
                ['question' => 'سؤال تجريبي 2', 'options' => ['أ', 'ب', 'ج', 'د'], 'correct_answer' => 1],
            ],
            'points' => 20,
            'passing_score' => 60,
            'max_attempts' => 3,
            'order' => fake()->numberBetween(1, 50),
            'status' => 'active',
            'approval_status' => 'approved',
        ];
    }

    public function pendingApproval(): static
    {
        return $this->state(fn () => ['approval_status' => 'pending']);
    }

    public function quiz(array $questions = []): static
    {
        return $this->state(fn () => [
            'type' => 'quiz',
            'questions' => $questions ?: [
                ['question' => '1+1=', 'options' => ['1', '2', '3', '4'], 'correct_answer' => 1],
            ],
        ]);
    }

    public function essay(): static
    {
        return $this->state(fn () => [
            'type' => 'creative',
            'questions' => null,
        ]);
    }
}
