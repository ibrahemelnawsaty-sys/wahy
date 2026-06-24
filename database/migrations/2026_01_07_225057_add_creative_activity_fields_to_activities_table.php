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
        Schema::table('activities', function (Blueprint $table) {
            $table->boolean('is_creative')->default(false)->after('is_team_activity')->comment('نشاط إبداعي جماعي للفصل');
            $table->boolean('is_activity_bank')->default(false)->after('is_creative')->comment('هل النشاط في بنك الأنشطة');
            $table->integer('bonus_points')->default(0)->after('is_activity_bank')->comment('نقاط إضافية للنشاط الإبداعي');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['is_creative', 'is_activity_bank', 'bonus_points']);
        });
    }
};
