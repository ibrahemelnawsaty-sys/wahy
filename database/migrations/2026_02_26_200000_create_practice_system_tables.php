<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['quiz', 'review', 'challenge'])->default('quiz');
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->integer('time_limit')->nullable(); // دقائق
            $table->integer('max_attempts')->default(3);
            $table->boolean('is_active')->default(true);
            $table->json('questions'); // مصفوفة IDs من question_bank
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'is_active']);
            $table->index('classroom_id');
        });

        Schema::create('practice_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('exercise_id')->constrained('practice_exercises')->onDelete('cascade');
            $table->json('answers')->nullable();
            $table->integer('score')->default(0);
            $table->integer('total_questions')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->integer('time_taken')->nullable(); // ثواني
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'exercise_id']);
        });

        Schema::create('pvp_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->json('questions'); // مصفوفة IDs من question_bank
            $table->integer('time_limit')->default(30); // ثواني لكل سؤال
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('pvp_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained('pvp_challenges')->onDelete('cascade');
            $table->foreignId('player1_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('player2_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->json('player1_answers')->nullable();
            $table->json('player2_answers')->nullable();
            $table->integer('player1_score')->default(0);
            $table->integer('player2_score')->default(0);
            $table->integer('player1_time')->default(0);
            $table->integer('player2_time')->default(0);
            $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['waiting', 'playing', 'completed'])->default('waiting');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['challenge_id', 'status']);
            $table->index('player1_id');
            $table->index('player2_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pvp_matches');
        Schema::dropIfExists('pvp_challenges');
        Schema::dropIfExists('practice_attempts');
        Schema::dropIfExists('practice_exercises');
    }
};
