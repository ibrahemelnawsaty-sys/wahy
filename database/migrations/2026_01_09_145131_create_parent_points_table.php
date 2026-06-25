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
        Schema::create('parent_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id');
            $table->integer('points');
            $table->string('reason');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['parent_id', 'created_at']);
        });

        Schema::create('parent_praises', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id');
            $table->unsignedBigInteger('student_id');
            $table->text('praise_message');
            $table->enum('praise_type', ['encouragement', 'achievement', 'behavior', 'custom']);
            $table->integer('points_awarded')->default(5);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['student_id', 'created_at']);
        });

        Schema::create('parent_gifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id');
            $table->unsignedBigInteger('student_id');
            $table->string('gift_type');
            $table->text('gift_message')->nullable();
            $table->integer('points_cost')->default(10);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_gifts');
        Schema::dropIfExists('parent_praises');
        Schema::dropIfExists('parent_points');
    }
};
