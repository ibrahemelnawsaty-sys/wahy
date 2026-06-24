<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // المراحل الدراسية (ابتدائي، متوسط، ثانوي)
        Schema::create('education_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم المرحلة
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // السنوات الدراسية (الأول ابتدائي، الثاني متوسط...)
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('education_level_id')->constrained('education_levels')->cascadeOnDelete();
            $table->string('name'); // اسم السنة
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // ربط المدارس بالمراحل الدراسية
        Schema::create('school_education_level', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('education_level_id')->constrained('education_levels')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['school_id', 'education_level_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_education_level');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('education_levels');
    }
};
