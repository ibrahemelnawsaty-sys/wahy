<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إضافة user_id إلى settings لدعم إعدادات لكل معلّم/مستخدم
     * (null = إعداد عام للمنصة).
     * تغيير الـ unique على (key) إلى unique مركّب (key, user_id).
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')
                    ->constrained('users')->nullOnDelete();
            }
        });

        // تغيير قيد التفرّد: إسقاط الـ unique على key المنفرد إن وجد
        try {
            DB::statement('ALTER TABLE settings DROP INDEX settings_key_unique');
        } catch (\Throwable $e) {
            // index غير موجود — تجاهل
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->unique(['key', 'user_id'], 'settings_key_user_unique');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique('settings_key_user_unique');
        });

        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
            $table->unique('key');
        });
    }
};
