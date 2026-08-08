<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * الدفعة 2 (مهامّ 6/8/9/10): أُعيدت كتابة أقسام «المزايا» و«المنهجية» و«الفرق» في القالب.
 * نمسح تخصيصات landing_content القديمة لهذه الأقسام كي لا يطغى المحفوظ على المحتوى الجديد
 * للزوّار عبر سكربت /api/landing/content (R3) — القالب صار مصدر الحقيقة لهذه الأقسام.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('landing_content')) {
            DB::table('landing_content')
                ->whereIn('section', ['features', 'values', 'teams'])
                ->delete();
        }
    }

    public function down(): void
    {
        // لا استعادة: المحتوى القديم مستبدَل عمداً بمحتوى الـPDF الجديد.
    }
};
