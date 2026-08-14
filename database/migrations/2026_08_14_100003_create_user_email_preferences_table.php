<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * تفضيلات بريد المستخدم وإلغاء الاشتراك (خطّة البريد P4). الفئات الحرِجة (auth/transactional)
 * تُرسَل دائمًا؛ هذه تتحكّم في الملخّصات/الإعلانات/إشعارات الأحداث فقط.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_email_preferences')) {
            return;
        }

        Schema::create('user_email_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->boolean('unsubscribed_all')->default(false);
            $table->boolean('digests')->default(true);        // الملخّصات الدوريّة
            $table->boolean('announcements')->default(true);  // الإعلانات/الحملات
            $table->boolean('events')->default(true);         // إشعارات الأحداث
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_email_preferences');
    }
};
