<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * توحيد عمود activity_submissions.status كسلسلة على كل المحرّكات — يُزيل تباين enum
 * المقصور على MySQL (قيمة 'completed' كانت تُضاف عبر ALTER MySQL فقط في هجرة
 * 2026_02_12، فترفضها SQLite أو أيّ بيئة إنتاج لم تُطبَّق عليها الهجرة → خطأ حفظ صامت/500).
 *
 * المصدر الوحيد للحالات الصالحة يصبح طبقة التطبيق (ActivitySubmission::PENDING_REVIEW_STATUSES
 * وأخواتها)، لا قيدٌ على مستوى القاعدة متباينٌ بين المحرّكين. (بند #15 من docs/ACTIVITIES_DIAGNOSIS.md)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_submissions', function (Blueprint $table) {
            $table->string('status', 32)->default('pending')->change();
        });
    }

    public function down(): void
    {
        // غير رجعيّ عمداً — لا نُعيد قيد enum المتباين.
    }
};
