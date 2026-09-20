<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جنس المستخدم لتكييف صيغة الخطاب العربيّ (مذكّر/مؤنّث). قابلٌ للـnull: الحسابات الحاليّة
 * وحسابات الأدمن المُنشأة تبقى بلا قيمة → تُخاطَب بالمذكّر (السلوك الحاليّ) حتى تختار في ملفّها.
 * التسجيل الذاتيّ الجديد يفرضه. القيم: 'male' | 'female'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('gender', 10)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('gender');
        });
    }
};
