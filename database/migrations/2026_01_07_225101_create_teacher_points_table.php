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
        Schema::create('teacher_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->integer('points')->default(0)->comment('إجمالي نقاط المعلم');
            $table->integer('students_total_points')->default(0)->comment('إجمالي نقاط طلاب المعلم');
            $table->integer('students_count')->default(0)->comment('عدد طلاب المعلم');
            $table->integer('activities_created')->default(0)->comment('عدد الأنشطة المنشأة');
            $table->integer('questions_approved')->default(0)->comment('عدد الأسئلة المعتمدة');
            $table->timestamps();
            
            $table->unique('teacher_id');
            $table->index(['points', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_points');
    }
};
