<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<ActivitySubmission>
 */
class ActivitySubmissionFactory extends Factory
{
    protected $model = ActivitySubmission::class;

    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'student_id' => User::factory()->student(),
            'answer' => json_encode([0, 1]),
            // 'pending' متاح في كل من MySQL و SQLite (الـ enum الأصلي)
            'status' => 'pending',
            'score' => fake()->numberBetween(0, 100),
            'submitted_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'score' => null,
        ]);
    }

    public function approved(int $score = 90): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'score' => $score,
        ]);
    }
}
