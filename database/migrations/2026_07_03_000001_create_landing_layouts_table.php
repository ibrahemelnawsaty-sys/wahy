<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * تخطيط الصفحة الرئيسية (المحرّر المرئي المدمج).
 *
 * على عكس landing_content (قيم نصوص/صور مفتاحية فقط)، هذا الجدول يحفظ
 * لقطة HTML كاملة ومُعقّمة لمحتوى <main> بعد التعديل — تلتقط البنية
 * (سحب/حذف/تكرار/ترتيب الأقسام) والأنماط المضمّنة والنصوص دفعةً واحدة.
 * سطر واحد فعّال لكل صفحة (key)، والقالب الثابت يبقى fallback عند الغياب.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->default('home'); // يدعم صفحات متعددة مستقبلاً
            $table->longText('html')->nullable();              // لقطة <main> المُعقّمة
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_layouts');
    }
};
