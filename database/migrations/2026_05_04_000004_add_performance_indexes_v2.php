<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes إضافية للاستعلامات الأكثر استخداماً.
 *
 * - activity_submissions(student_id, status): تسريع تقارير "النشاط مكتمل"
 * - activity_submissions(student_id, activity_id): تسريع whereDoesntHave
 * - points(user_id, created_at): تسريع leaderboard بالفترة
 * - parent_student(parent_id, student_id): تسريع علاقات ولي الأمر
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->safeIndex('activity_submissions', 'idx_subs_student_status', ['student_id', 'status']);
        $this->safeIndex('activity_submissions', 'idx_subs_student_activity', ['student_id', 'activity_id']);
        $this->safeIndex('points', 'idx_points_user_created', ['user_id', 'created_at']);

        if (Schema::hasTable('parent_student')) {
            $this->safeIndex('parent_student', 'idx_ps_parent_student', ['parent_id', 'student_id']);
        }

        if (Schema::hasTable('teacher_points')) {
            $this->safeIndex('teacher_points', 'idx_tp_teacher_created', ['teacher_id', 'created_at']);
        }

        if (Schema::hasTable('parent_points')) {
            $this->safeIndex('parent_points', 'idx_pp_parent_created', ['parent_id', 'created_at']);
        }
    }

    public function down(): void
    {
        $this->dropIndex('activity_submissions', 'idx_subs_student_status');
        $this->dropIndex('activity_submissions', 'idx_subs_student_activity');
        $this->dropIndex('points', 'idx_points_user_created');
        $this->dropIndex('parent_student', 'idx_ps_parent_student');
        $this->dropIndex('teacher_points', 'idx_tp_teacher_created');
        $this->dropIndex('parent_points', 'idx_pp_parent_created');
    }

    private function safeIndex(string $table, string $indexName, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        // تحقق أن كل الأعمدة موجودة
        foreach ($columns as $col) {
            if (! Schema::hasColumn($table, $col)) {
                return;
            }
        }

        try {
            $exists = collect(DB::select(
                "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
                [$indexName],
            ))->isNotEmpty();

            if (! $exists) {
                Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                    $t->index($columns, $indexName);
                });
            }
        } catch (\Throwable $e) {
            // database لا تدعم SHOW INDEX (مثلاً SQLite في الاختبارات) — تجاهل
        }
    }

    private function dropIndex(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }
        try {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropIndex($indexName);
            });
        } catch (\Throwable $e) {
            // index غير موجود — تجاهل
        }
    }
};
