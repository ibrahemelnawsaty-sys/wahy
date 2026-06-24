<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تنظيف sessions عند حذف user.
 *
 * sessions.user_id كان nullable+index فقط بدون foreign constraint.
 * عند حذف user، تبقى sessions يتيمة في DB (تُنظَّف أحياناً عبر session GC).
 * نضيف foreign key مع nullOnDelete لضمان استمرارية الجلسات الضيف وتنظيف الجلسات
 * المرتبطة بمستخدمين محذوفين.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sessions') || !Schema::hasColumn('sessions', 'user_id')) {
            return;
        }

        // محاولة إضافة FK — لو فشل (لأن السجلات اليتيمة موجودة)، ننظفها أولاً
        try {
            // نظف اليتامى أولاً لتجنب فشل إنشاء الـ FK
            DB::table('sessions')
                ->whereNotNull('user_id')
                ->whereNotIn('user_id', function ($q) {
                    $q->select('id')->from('users');
                })
                ->update(['user_id' => null]);

            Schema::table('sessions', function (Blueprint $table) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        } catch (\Throwable $e) {
            // لو الـ FK موجود مسبقاً أو DB لا يدعم — تجاهل
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('sessions')) {
            return;
        }

        try {
            Schema::table('sessions', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Throwable $e) {
            // FK غير موجود — تجاهل
        }
    }
};
