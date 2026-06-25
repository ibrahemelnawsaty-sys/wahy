<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_statistics_cache', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // school, teacher, student
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('school_id')->nullable();

            // Point tracking
            $table->bigInteger('total_points')->default(0);
            $table->bigInteger('previous_points')->default(0);
            $table->bigInteger('points_change')->default(0);
            $table->bigInteger('monthly_points')->default(0);

            // Rankings
            $table->integer('platform_rank')->nullable();
            $table->integer('country_rank')->nullable();
            $table->integer('city_rank')->nullable();
            $table->integer('grade_rank')->nullable();

            // Totals per scope
            $table->integer('platform_total')->default(0);
            $table->integer('country_total')->default(0);
            $table->integer('city_total')->default(0);
            $table->integer('grade_total')->default(0);

            // Trend (up, down, same)
            $table->string('trend', 10)->default('same');
            $table->integer('rank_change')->default(0);

            // Extra
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('grade_level')->nullable();

            // Badges JSON
            $table->json('badges')->nullable();

            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['school_id', 'entity_type']);
            $table->index(['country', 'entity_type']);
            $table->index(['city', 'entity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_statistics_cache');
    }
};
