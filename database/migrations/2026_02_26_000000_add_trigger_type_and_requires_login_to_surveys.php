<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            if (!Schema::hasColumn('surveys', 'trigger_type')) {
                $table->string('trigger_type')->default('manual')->after('status');
                // on_platform_open = عند فتح المنصة
                // on_login = عند تسجيل الدخول
                // on_first_login = عند أول تسجيل دخول
                // on_lesson_start = عند بدء الدرس
                // on_lesson_complete = عند إتمام الدرس
                // on_activity_complete = عند إتمام النشاط
                // manual = يدوي (يظهر عند فتح الرابط مباشرة)
            }
            if (!Schema::hasColumn('surveys', 'requires_login')) {
                $table->boolean('requires_login')->default(true)->after('trigger_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn(['trigger_type', 'requires_login']);
        });
    }
};
