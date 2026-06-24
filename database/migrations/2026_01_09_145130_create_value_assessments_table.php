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
        Schema::table('values', function (Blueprint $table) {
            $table->boolean('pre_assessment_required')->default(true)->after('description');
            $table->boolean('post_assessment_required')->default(true)->after('pre_assessment_required');
        });

        Schema::create('value_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('value_id');
            $table->unsignedBigInteger('student_id');
            $table->enum('assessment_type', ['pre', 'post']);
            $table->integer('score')->default(0);
            $table->json('answers')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('value_id')->references('id')->on('values')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['value_id', 'student_id', 'assessment_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('value_assessments');
        
        Schema::table('values', function (Blueprint $table) {
            $table->dropColumn(['pre_assessment_required', 'post_assessment_required']);
        });
    }
};
