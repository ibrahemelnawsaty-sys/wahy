<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * شفاءٌ ثانٍ لأنشطة عالقة على all_schools_mode='none' فمخفيّة عن كل الطلاب.
 *
 * السبب: ActivityManagementController::store (وشقيقه) كان لا يضبط all_schools_mode بعد
 * إدخال نموذج النشر، فأنشطةُ الأدمن (نشاط درسٍ معتمَد) التي أُنشئت بين هجرة النشر الأصليّة
 * وإصلاح المتحكّم بقيت 'none' → غير مرئيّة (انحدار). هذه الهجرة تُعيد تطبيق **نفس شرط**
 * الشفاء التوافقيّ الأصليّ (2026_07_22_000001) على تلك الصفوف — idempotent وآمن:
 * تستثني قوالبَ البنك المعزولة فعلاً (is_activity_bank=true وبلا درس) كي لا تُفتَح بتخمين id.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('activities', 'all_schools_mode')) {
            return;
        }

        DB::table('activities')
            ->where('approval_status', 'approved')
            ->where('all_schools_mode', 'none')
            ->where(function ($q) {
                $q->where('is_activity_bank', false)
                    ->orWhereNotNull('lesson_id');
            })
            ->update(['all_schools_mode' => 'direct']);
    }

    public function down(): void
    {
        // لا تراجُع: لا يمكن تمييز الصفوف المُشفاة عن المنشورة مباشرةً أصلاً (كلاهما 'direct').
    }
};
