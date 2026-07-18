<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إسناد عدّة مدارس لمدير المدرسة (تعدّد المدارس).
 * السوبر أدمن يُسند، والمدير يبدّل «المدرسة النشطة» عبر الجلسة (لا يُلمس عمود users.school_id المحروس).
 * توافق خلفيّ: مديرو مدرسة واحدة بلا صفوف هنا يعملون عبر users.school_id (managedSchoolIds ∪ school_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_schools')) {
            return;
        }

        Schema::create('admin_schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'school_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_schools');
    }
};
