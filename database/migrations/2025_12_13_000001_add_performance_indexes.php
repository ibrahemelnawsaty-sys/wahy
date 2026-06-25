<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * إضافة indexes لتحسين أداء الاستعلامات
     */
    public function up(): void
    {
        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            // Index مركب للاستعلامات المتكررة
            $table->index(['school_id', 'role', 'status'], 'users_school_role_status_idx');
            $table->index(['role', 'status'], 'users_role_status_idx');
            $table->index('status', 'users_status_idx');
        });

        // Activity Submissions indexes
        Schema::table('activity_submissions', function (Blueprint $table) {
            $table->index(['student_id', 'status'], 'activity_submissions_student_status_idx');
            $table->index(['student_id', 'created_at'], 'activity_submissions_student_date_idx');
            $table->index('status', 'activity_submissions_status_idx');
        });

        // Points table indexes
        Schema::table('points', function (Blueprint $table) {
            $table->index('user_id', 'points_user_idx');
        });

        // Classrooms indexes
        Schema::table('classrooms', function (Blueprint $table) {
            $table->index(['school_id', 'status'], 'classrooms_school_status_idx');
            $table->index('teacher_id', 'classrooms_teacher_idx');
        });

        // Registration Requests indexes
        Schema::table('registration_requests', function (Blueprint $table) {
            $table->index(['school_id', 'status'], 'registration_requests_school_status_idx');
        });

        // Page Builder index
        Schema::table('page_builder', function (Blueprint $table) {
            $table->index(['slug', 'is_active'], 'page_builder_slug_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_school_role_status_idx');
            $table->dropIndex('users_role_status_idx');
            $table->dropIndex('users_status_idx');
        });

        Schema::table('activity_submissions', function (Blueprint $table) {
            $table->dropIndex('activity_submissions_student_status_idx');
            $table->dropIndex('activity_submissions_student_date_idx');
            $table->dropIndex('activity_submissions_status_idx');
        });

        Schema::table('points', function (Blueprint $table) {
            $table->dropIndex('points_user_idx');
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropIndex('classrooms_school_status_idx');
            $table->dropIndex('classrooms_teacher_idx');
        });

        Schema::table('registration_requests', function (Blueprint $table) {
            $table->dropIndex('registration_requests_school_status_idx');
        });

        Schema::table('page_builder', function (Blueprint $table) {
            $table->dropIndex('page_builder_slug_active_idx');
        });
    }
};
