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
        Schema::table('lessons', function (Blueprint $table) {
            // إعدادات الـ Streak للدرس
            $table->integer('streak_min_days')->nullable()->after('points'); // الحد الأدنى من الأيام
            $table->integer('streak_max_days')->nullable()->after('streak_min_days'); // الحد الأعلى من الأيام
            $table->integer('streak_bonus_points')->default(0)->after('streak_max_days'); // نقاط المكافأة
            $table->boolean('streak_enabled')->default(false)->after('streak_bonus_points'); // تفعيل نظام الـ streak
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['streak_min_days', 'streak_max_days', 'streak_bonus_points', 'streak_enabled']);
        });
    }
};
