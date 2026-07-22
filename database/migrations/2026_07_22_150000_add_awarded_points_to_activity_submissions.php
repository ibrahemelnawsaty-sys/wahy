<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * #13: منح مكافأة النشاط على «أفضل محاولة» مرّة واحدة (لا تراكم عبر المحاولات).
 *
 * awarded_points = أعلى XP مُنِح للطالب على هذا التسليم حتى الآن. عند كل إعادة تسليم نمنح
 * فقط الفرق التصاعديّ (currentXp − awarded_points) إن كان موجبًا — فلا تُضاعَف النقاط/العملات
 * بإعادة نشاطٍ ناجح، ولا يخسر الطالب أفضل نتيجة إن تراجع. العملات تُشتقّ من awarded_points.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('activity_submissions', 'awarded_points')) {
            Schema::table('activity_submissions', function (Blueprint $table) {
                $table->unsignedInteger('awarded_points')->default(0)->after('score');
            });
        }

        // توافق: التسليمات القائمة المُصحَّحة آليًّا (score غير فارغ) مُنِحت XP فعلاً وفق
        // الصيغة round(score% × نقاط النشاط). نضبط awarded_points لها كي لا تُعيد الإعادة منحًا
        // مزدوجًا. نحتاج نقاط النشاط عبر ربط الأنشطة (UPDATE…JOIN) — مدعوم على MySQL (الإنتاج).
        // على SQLite (بيئة الاختبار) الجداول فارغة وقت الهجرة فلا حاجة له، ونتفادى صياغة JOIN
        // غير المدعومة. idempotent (يشترط awarded_points=0).
        if (Schema::hasColumn('activity_submissions', 'awarded_points')
            && \Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::table('activity_submissions')
                ->join('activities', 'activity_submissions.activity_id', '=', 'activities.id')
                ->whereNotNull('activity_submissions.score')
                ->where('activity_submissions.awarded_points', 0)
                ->update([
                    'activity_submissions.awarded_points' => \Illuminate\Support\Facades\DB::raw(
                        'ROUND((activity_submissions.score / 100) * COALESCE(activities.points, 10))'
                    ),
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('activity_submissions', 'awarded_points')) {
            Schema::table('activity_submissions', function (Blueprint $table) {
                $table->dropColumn('awarded_points');
            });
        }
    }
};
