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
        Schema::create('activity_user_streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('completed_days')->default(0);
            $table->json('activity_dates')->nullable(); // تواريخ الأيام المكتملة
            $table->boolean('bonus_claimed')->default(false);
            $table->date('last_activity_date')->nullable();
            $table->integer('total_bonus_earned')->default(0); // إجمالي المكافآت
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_user_streaks');
    }
};
