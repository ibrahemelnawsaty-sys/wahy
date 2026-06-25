<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - تحويل activities.type و lessons.type من enum إلى string لإزالة تصلّب enum الذي كان
 *   يسبب خطأ 500 عند إنشاء أنواع مسموحة في التحقق لكنها خارج enum
 *   (creative/image_order/homework/practice للأنشطة، audio للدروس).
 * - إضافة عمودَي difficulty و coins لجدول activities (كانا يُرسَلان ويُفقَدان صامتاً).
 * القيم المسموحة تُضبط الآن في طبقة التحقق (validation in:) لا في قيد قاعدة البيانات.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('type', 30)->default('quiz')->change();
            if (! Schema::hasColumn('activities', 'difficulty')) {
                $table->string('difficulty', 20)->nullable()->after('question_type');
            }
            if (! Schema::hasColumn('activities', 'coins')) {
                $table->integer('coins')->default(0)->after('points');
            }
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->string('type', 20)->default('text')->change();
        });
    }

    public function down(): void
    {
        // لا نُعيد enum (قد تحوي البيانات قيماً جديدة)؛ نكتفي بإسقاط الأعمدة المضافة.
        Schema::table('activities', function (Blueprint $table) {
            foreach (['difficulty', 'coins'] as $col) {
                if (Schema::hasColumn('activities', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
