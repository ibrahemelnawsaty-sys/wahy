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
        Schema::create('family_activity_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('parent_id');
            $table->json('submission_data')->nullable();
            $table->json('photos')->nullable();
            $table->boolean('parent_approved')->default(false);
            $table->timestamp('parent_approved_at')->nullable();
            $table->text('parent_praise')->nullable();
            $table->timestamps();

            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['student_id', 'parent_approved']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_activity_submissions');
    }
};
