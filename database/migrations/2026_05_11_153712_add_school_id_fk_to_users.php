<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * إضافة Foreign Key على users.school_id (كان مفقوداً منذ migration الأصلي).
 *
 * 🔴 خطوة حماية: تنظيف users اليتيمين قبل إضافة FK لتجنب فشل المهجرة.
 *    الـ orphan = user يشير لـ school_id لمدرسة محذوفة.
 *    نضع لهم school_id = NULL (لا نحذف المستخدمين).
 *
 * ⚠️  قبل التشغيل في الإنتاج: خذ نسخة احتياطية كاملة من قاعدة البيانات.
 */
return new class extends Migration {
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // 1) فحص وجود FK مسبقاً (MySQL only — في SQLite لا يوجد information_schema)
        if ($driver === 'mysql') {
            $hasFk = collect(DB::select(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'users'
                   AND COLUMN_NAME = 'school_id'
                   AND REFERENCED_TABLE_NAME = 'schools'"
            ))->isNotEmpty();

            if ($hasFk) {
                Log::info('FK users.school_id موجود مسبقاً — تخطّي المهجرة');
                return;
            }
        }

        // 2) تنظيف اليتيمين أولاً
        $orphanCount = DB::table('users')
            ->whereNotNull('school_id')
            ->whereNotIn('school_id', function ($q) {
                $q->select('id')->from('schools');
            })
            ->count();

        if ($orphanCount > 0) {
            Log::warning("FK migration: تم العثور على {$orphanCount} مستخدم يتيم — يتم تعيين school_id = NULL");
            DB::table('users')
                ->whereNotNull('school_id')
                ->whereNotIn('school_id', function ($q) {
                    $q->select('id')->from('schools');
                })
                ->update(['school_id' => null]);
        }

        // 3) تأكد أن school_id يقبل NULL
        Schema::table('users', function (Blueprint $t) {
            $t->unsignedBigInteger('school_id')->nullable()->change();
        });

        // 4) إضافة الفهرس إن لم يكن موجوداً (FK سيستخدمه)
        try {
            Schema::table('users', function (Blueprint $t) {
                $t->index('school_id', 'idx_users_school_id');
            });
        } catch (\Throwable $e) {
            // duplicate index — تجاهل
        }

        // 5) إضافة FK
        Schema::table('users', function (Blueprint $t) {
            $t->foreign('school_id', 'fk_users_school_id')
                ->references('id')
                ->on('schools')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            try {
                $t->dropForeign('fk_users_school_id');
            } catch (\Throwable $e) {
                // غير موجود — تجاهل
            }
        });
    }
};
