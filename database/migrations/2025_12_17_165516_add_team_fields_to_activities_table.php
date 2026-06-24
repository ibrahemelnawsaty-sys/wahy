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
            $table->boolean('is_team_activity')->default(false)->after('is_homework');
            $table->integer('min_team_size')->nullable()->after('is_team_activity');
            $table->integer('max_team_size')->nullable()->after('min_team_size');
            $table->boolean('allow_team_formation')->default(true)->after('max_team_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['is_team_activity', 'min_team_size', 'max_team_size', 'allow_team_formation']);
        });
    }
};
