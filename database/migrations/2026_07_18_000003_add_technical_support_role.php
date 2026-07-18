<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * توسيع users.role من enum إلى string ليستوعب الدور الجديد 'technical_support'
 * (الدعم الفنيّ) وأي أدوار مستقبلية — نظير سابقة pvp_matches.status.
 *
 * لماذا التحويل إلى string بدل ALTER enum؟ ALTER enum يختلف سلوكه بشدّة عبر
 * السائقين (MySQL يدعمه، SQLite لا enum أصلاً) ويؤلم في الترحيل. تحويل النوع
 * إلى string مرن يستوعب القيمة الجديدة بلا فقدان بيانات (القيم الحالية تبقى
 * كما هي) وينجح على MySQL وSQLite (اختبارات RefreshDatabase).
 *
 * لا نُدرِج قيمة هنا؛ فقط توسيع النوع.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->change();
        });
    }

    public function down(): void
    {
        // إعادة القيد ENUM على MySQL فقط (SQLite لا enum له). قد تفشل إن وُجدت
        // صفوف بدور 'technical_support'؛ لذا نُبقيها اختيارية ومحصورة بـ MySQL.
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        // نُنزِّل أيّ حسابات دعم فنيّ إلى student قبل تضييق الـenum، وإلا يفشل التغيير
        // بـ«Data truncated» على القيمة غير المُدرَجة في القائمة الجديدة.
        DB::table('users')->where('role', 'technical_support')->update(['role' => 'student']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'school_admin', 'teacher', 'student', 'parent'])
                ->default('student')->change();
        });
    }
};
