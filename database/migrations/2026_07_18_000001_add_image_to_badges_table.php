<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إضافة عمود صورة اختياري للشارة (رفع من إدارة الأدمن).
     * آمنة (hasColumn) — الأيقونة (emoji) تبقى، والصورة بديل بصريّ اختياريّ.
     */
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            if (! Schema::hasColumn('badges', 'image')) {
                $table->string('image')->nullable()->after('icon');
            }
        });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            if (Schema::hasColumn('badges', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};
