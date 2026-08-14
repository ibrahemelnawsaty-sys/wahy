<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * تغذية راجعة: خيار «استخدم هيدر/فوتر الموقع الرئيسيّ» في الصفحات الثانوية — يُصيَّر هيدر/فوتر
 * مُوحَّد مع الموقع (نفس مفاتيح lc/الإعدادات) يُعدَّل من «محتوى الصفحة الرئيسية».
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pb_pages', function (Blueprint $table) {
            $table->boolean('use_site_header')->default(false)->after('hide_footer');
            $table->boolean('use_site_footer')->default(false)->after('use_site_header');
        });
    }

    public function down(): void
    {
        Schema::table('pb_pages', function (Blueprint $table) {
            $table->dropColumn(['use_site_header', 'use_site_footer']);
        });
    }
};
