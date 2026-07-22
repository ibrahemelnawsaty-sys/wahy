<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * نشر الأنشطة متعدّد المدارس (المرحلة 1 — المخطّط).
 *
 * يفصل «أين/لمن يُنشر النشاط» عن «هل هو معتمَد»:
 *  - activity_school: صفّ لكل مدرسة نُشِر لها النشاط + وضع النشر (بنك/مباشر).
 *  - all_schools_mode: نشر الأدمن «لكل المدارس» (none/bank/direct) — بديل صريح عن
 *    اصطلاح «الـpivot الفارغ = الكل» الغامض.
 *
 * هجرة آمنة (create table + add column، عكسها بسيط) + إصلاح بيانات توافقيّ idempotent
 * (الأنشطة المعتمَدة القائمة كانت مرئيّة عالميّاً → تبقى كذلك عبر all_schools_mode='direct').
 */
return new class extends Migration
{
    public function up(): void
    {
        // (1) عمود «النشر لكل المدارس» على الأنشطة
        if (! Schema::hasColumn('activities', 'all_schools_mode')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->enum('all_schools_mode', ['none', 'bank', 'direct'])
                    ->default('none')
                    ->after('school_rejection_reason');
            });
        }

        // (2) جدول ربط النشاط بالمدارس (النشر المُوجَّه) — على غرار school_active_values
        if (! Schema::hasTable('activity_school')) {
            Schema::create('activity_school', function (Blueprint $table) {
                $table->id();
                $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
                $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
                // وضع النشر لهذه المدرسة تحديداً (قد يختلف بين المدارس)
                $table->enum('publish_mode', ['bank', 'direct'])->default('bank');
                $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
                $table->unique(['activity_id', 'school_id']);
            });
        }

        // (3) إصلاح توافقيّ idempotent: الأنشطة المعتمَدة القائمة كانت مرئيّة للطلاب
        //     عالميّاً (approval_status='approved') → نُبقي ذلك بجعلها «مباشر لكل المدارس».
        //     تُشغَّل مرّة أو أكثر بنفس النتيجة (شرط approval_status='approved' فقط).
        DB::table('activities')
            ->where('approval_status', 'approved')
            ->where('all_schools_mode', 'none')
            ->update(['all_schools_mode' => 'direct']);
    }

    public function down(): void
    {
        if (Schema::hasTable('activity_school')) {
            Schema::dropIfExists('activity_school');
        }
        if (Schema::hasColumn('activities', 'all_schools_mode')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->dropColumn('all_schools_mode');
            });
        }
    }
};
