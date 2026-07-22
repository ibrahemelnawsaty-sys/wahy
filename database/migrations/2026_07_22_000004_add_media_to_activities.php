<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * «الوسائط المتعددة» للنشاط: عمود JSON يخزّن عدّة ملفّات (فيديو/صوت/صورة/مستند) لكلّ نشاط —
 * كلٌّ [{type, path, name}]. يستبدل عمود attachment المفرد (الذي يبقى للتوافق الخلفيّ فيُعرَض
 * إن وُجد). هجرة آمنة (add column nullable، عكسها بسيط).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('activities', 'media')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->json('media')->nullable()->after('attachment');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('activities', 'media')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->dropColumn('media');
            });
        }
    }
};
