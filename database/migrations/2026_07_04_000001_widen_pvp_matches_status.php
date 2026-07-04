<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * توسيع pvp_matches.status.
 *
 * أُنشئ العمود كـ ENUM('waiting','playing','completed') في هجرة نظام التمرين
 * (2026_02_26)، فيرفض حالتَي التحدي الموجّه الجديدتين: 'invited' (دعوة معلّقة)
 * و'declined' (رُفضت). نحوّله إلى VARCHAR مرن يستوعبها وأي حالات مستقبلية،
 * بلا فقدان بيانات (القيم الحالية تبقى كما هي).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pvp_matches')) {
            return;
        }

        Schema::table('pvp_matches', function (Blueprint $table) {
            $table->string('status', 20)->default('waiting')->change();
        });
    }

    public function down(): void
    {
        // لا نُعيد القيد ENUM: قد توجد صفوف بحالات 'invited'/'declined' تفشل.
    }
};
