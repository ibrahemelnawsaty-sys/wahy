<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة الفهارس الناقصة المكتشفة في تدقيق الأداء (Sprint 1).
 *
 * يُغطّي:
 *  - polymorphic morphs على notifications و activity_log
 *  - FK columns على activities (created_by, classroom_id) و lessons (concept_id)
 *  - composite indexes شائعة الاستخدام
 *
 * كل index مغلّف بـ try/catch لأن بعض الفهارس قد تكون موجودة سابقاً
 * من migrations الأداء السابقة (لا نريد فشل المهجرة كاملة).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->safeAdd('notifications', function (Blueprint $t) {
            $t->index(['notifiable_type', 'notifiable_id'], 'idx_notifications_morph');
        });

        $this->safeAdd('activity_log', function (Blueprint $t) {
            $t->index(['subject_type', 'subject_id'], 'idx_activity_log_subject');
            $t->index(['causer_type', 'causer_id'], 'idx_activity_log_causer');
        });

        $this->safeAdd('activities', function (Blueprint $t) {
            $t->index('created_by', 'idx_activities_created_by');
            $t->index('classroom_id', 'idx_activities_classroom_id');
        });

        // lessons.concept_id أُضيف بعد إزالة meanings — قد لا يكون مفهرساً
        if (Schema::hasColumn('lessons', 'concept_id')) {
            $this->safeAdd('lessons', function (Blueprint $t) {
                $t->index('concept_id', 'idx_lessons_concept_id');
            });
        }

        // ActivitySubmission lookups شائعة
        $this->safeAdd('activity_submissions', function (Blueprint $t) {
            $t->index(['student_id', 'activity_id', 'status'], 'idx_subm_student_act_status');
        });

        // school_active_values pivot
        if (Schema::hasTable('school_active_values')) {
            $this->safeAdd('school_active_values', function (Blueprint $t) {
                $t->index(['school_id', 'value_id'], 'idx_school_active_values');
            });
        }
    }

    public function down(): void
    {
        $this->safeDrop('notifications', 'idx_notifications_morph');
        $this->safeDrop('activity_log', 'idx_activity_log_subject');
        $this->safeDrop('activity_log', 'idx_activity_log_causer');
        $this->safeDrop('activities', 'idx_activities_created_by');
        $this->safeDrop('activities', 'idx_activities_classroom_id');
        $this->safeDrop('lessons', 'idx_lessons_concept_id');
        $this->safeDrop('activity_submissions', 'idx_subm_student_act_status');
        $this->safeDrop('school_active_values', 'idx_school_active_values');
    }

    /**
     * إضافة فهرس بأمان — يلتقط استثناء "Duplicate key" بدون كسر المهجرة.
     */
    private function safeAdd(string $table, \Closure $callback): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, $callback);
        } catch (\Throwable $e) {
            // تجاهل "Duplicate key name" — index موجود مسبقاً
            if (! str_contains(strtolower($e->getMessage()), 'duplicate')) {
                throw $e;
            }
        }
    }

    /**
     * حذف فهرس بأمان (للـ rollback).
     */
    private function safeDrop(string $table, string $index): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $t) use ($index) {
                $t->dropIndex($index);
            });
        } catch (\Throwable $e) {
            // index ليس موجوداً — تجاهل
        }
    }
};
