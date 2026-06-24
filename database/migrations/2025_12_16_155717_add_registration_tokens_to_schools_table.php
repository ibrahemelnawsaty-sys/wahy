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
        Schema::table('schools', function (Blueprint $table) {
            $table->string('teacher_token')->unique()->nullable()->after('status');
            $table->string('student_token')->unique()->nullable()->after('teacher_token');
            $table->string('parent_token')->unique()->nullable()->after('student_token');
            $table->text('teacher_qr')->nullable()->after('parent_token');
            $table->text('student_qr')->nullable()->after('teacher_qr');
            $table->text('parent_qr')->nullable()->after('student_qr');
            $table->boolean('enable_teacher_registration')->default(true)->after('parent_qr');
            $table->boolean('enable_student_registration')->default(true)->after('enable_teacher_registration');
            $table->boolean('enable_parent_registration')->default(true)->after('enable_student_registration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'teacher_token',
                'student_token',
                'parent_token',
                'teacher_qr',
                'student_qr',
                'parent_qr',
                'enable_teacher_registration',
                'enable_student_registration',
                'enable_parent_registration',
            ]);
        });
    }
};
