<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إسناد نشاط البنك لفصل «بلا نسخ» (المرحلة 4ب — «اختيار المعلّم من البنك: مرجع»).
 *
 * صفّ لكل (نشاط، فصل) يُسنده المعلّم من البنك المشترك لفصله دون تكرار النشاط. طلاب الفصل
 * المُسنَد إليه يرونه عبر توسيع بوّابة الرؤية (scopeVisibleToStudent) — عزل بعضويّة الفصل.
 * هجرة آمنة (create table، عكسها بسيط).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activity_classroom')) {
            Schema::create('activity_classroom', function (Blueprint $table) {
                $table->id();
                $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
                $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['activity_id', 'classroom_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_classroom');
    }
};
