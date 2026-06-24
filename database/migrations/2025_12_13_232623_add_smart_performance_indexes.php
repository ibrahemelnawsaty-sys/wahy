<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Activity Submissions - أهم جدول للأداء
        $this->addIndexSafely('activity_submissions', 'idx_student_status', ['student_id', 'status']);
        $this->addIndexSafely('activity_submissions', 'idx_status_submitted', ['status', 'submitted_at']);
        $this->addIndexSafely('activity_submissions', 'idx_reviewed', ['reviewed_by', 'reviewed_at']);

        // Points - للحسابات السريعة
        $this->addIndexSafely('points', 'idx_user_points', ['user_id', 'points']);

        // Coins - للحسابات السريعة
        $this->addIndexSafely('coins', 'idx_user_coins', ['user_id', 'coins']);

        // Users - للبحث والفلترة
        $this->addIndexSafely('users', 'idx_role_school', ['role', 'school_id']);
        $this->addIndexSafely('users', 'idx_email_role', ['email', 'role']);

        // Classrooms - للعلاقات
        $this->addIndexSafely('classrooms', 'idx_school_teacher', ['school_id', 'teacher_id']);

        // Activities - للبحث
        $this->addIndexSafely('activities', 'idx_lesson_order', ['lesson_id', 'order']);
        $this->addIndexSafely('activities', 'idx_activity_status', ['status']);

        // Lessons - للترتيب
        $this->addIndexSafely('lessons', 'idx_meaning_order', ['meaning_id', 'order']);
        $this->addIndexSafely('lessons', 'idx_lesson_status', ['status']);

        // Notifications - للأداء
        // العمود الصحيح هو notifiable_id (من morphs) وليس user_id
        if (Schema::hasColumn('notifications', 'notifiable_id')) {
            $this->addIndexSafely('notifications', 'idx_notif_morph_read', ['notifiable_id', 'read_at', 'created_at']);
        }

        // Streaks - للبحث السريع
        $this->addIndexSafely('streaks', 'idx_user_current', ['user_id', 'current_streak']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexSafely('activity_submissions', 'idx_student_status');
        $this->dropIndexSafely('activity_submissions', 'idx_status_submitted');
        $this->dropIndexSafely('activity_submissions', 'idx_reviewed');
        $this->dropIndexSafely('points', 'idx_user_points');
        $this->dropIndexSafely('coins', 'idx_user_coins');
        $this->dropIndexSafely('users', 'idx_role_school');
        $this->dropIndexSafely('users', 'idx_email_role');
        $this->dropIndexSafely('classrooms', 'idx_school_teacher');
        $this->dropIndexSafely('activities', 'idx_lesson_order');
        $this->dropIndexSafely('activities', 'idx_activity_status');
        $this->dropIndexSafely('lessons', 'idx_meaning_order');
        $this->dropIndexSafely('lessons', 'idx_lesson_status');
        $this->dropIndexSafely('notifications', 'idx_user_read_created');
        $this->dropIndexSafely('streaks', 'idx_user_current');
    }
    
    /**
     * Add index safely (skip if exists)
     */
    private function addIndexSafely(string $table, string $indexName, array $columns): void
    {
        try {
            if (!$this->indexExists($table, $indexName)) {
                Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
                    $blueprint->index($columns, $indexName);
                });
            }
        } catch (\Exception $e) {
            // Index already exists, skip
        }
    }
    
    /**
     * Drop index safely (skip if not exists)
     */
    private function dropIndexSafely(string $table, string $indexName): void
    {
        try {
            if ($this->indexExists($table, $indexName)) {
                Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                    $blueprint->dropIndex($indexName);
                });
            }
        } catch (\Exception $e) {
            // Index doesn't exist, skip
        }
    }
    
    /**
     * Check if index exists for SQLite
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name=?", [$indexName]);
        return count($result) > 0;
    }
};
