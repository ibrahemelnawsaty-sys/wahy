<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lesson_user_streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade');
            $table->integer('completed_days')->default(0); // عدد الأيام التي أكمل فيها نشاط
            $table->json('activity_dates')->nullable(); // تواريخ الأيام المكتملة
            $table->date('last_activity_date')->nullable(); // آخر تاريخ أكمل فيه نشاط
            $table->boolean('bonus_claimed')->default(false); // هل حصل على المكافأة؟
            $table->timestamp('bonus_claimed_at')->nullable(); // متى حصل على المكافأة
            $table->timestamps();

            // كل طالب له سجل واحد لكل درس
            $table->unique(['user_id', 'lesson_id']);
            $table->index('user_id');
            $table->index('lesson_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_user_streaks');
    }
};
