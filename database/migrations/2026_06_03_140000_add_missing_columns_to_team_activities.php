<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة الأعمدة التي يكتبها/يقرؤها كود الفرق ولا توجد في الجدول الأصلي
 * (total_score, teacher_feedback, team_submission, team_file, submitted_at).
 * الجدول الأصلي يحوي score/feedback غير المستخدمَين في الكود. كان نظام الفرق ينهار
 * عند التعيين/التقييم بسبب هذه الأعمدة المفقودة (Issue حرج).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_activities', function (Blueprint $table) {
            if (!Schema::hasColumn('team_activities', 'total_score')) {
                $table->integer('total_score')->nullable()->after('status');
            }
            if (!Schema::hasColumn('team_activities', 'team_submission')) {
                $table->text('team_submission')->nullable()->after('total_score');
            }
            if (!Schema::hasColumn('team_activities', 'team_file')) {
                $table->string('team_file')->nullable()->after('team_submission');
            }
            if (!Schema::hasColumn('team_activities', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('team_file');
            }
            if (!Schema::hasColumn('team_activities', 'teacher_feedback')) {
                $table->text('teacher_feedback')->nullable()->after('submitted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('team_activities', function (Blueprint $table) {
            foreach (['total_score', 'team_submission', 'team_file', 'submitted_at', 'teacher_feedback'] as $col) {
                if (Schema::hasColumn('team_activities', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
