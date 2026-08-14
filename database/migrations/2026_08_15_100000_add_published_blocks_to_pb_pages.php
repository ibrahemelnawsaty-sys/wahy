<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * فصل المسودّة عن المنشور (بقيّة الدفعة 5): عمود published_blocks هو الجسم **المخدوم للجمهور**،
 * بينما blocks يبقى المسودّة العاملة التي يحرّرها الأدمن. فيُحرَّر الحيّ بأمان دون ظهوره حتى إعادة النشر.
 * الصفحات المنشورة سابقاً تُملأ published_blocks من blocks الحاليّة (كي لا يتغيّر عرضها).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pb_pages', function (Blueprint $table) {
            $table->json('published_blocks')->nullable()->after('blocks');
        });

        // الصفحات المنشورة: ثبّت المنشور = الحاليّ (منع تسرّب المسودّة أو تغيّر العرض).
        DB::table('pb_pages')->where('status', 'published')->update([
            'published_blocks' => DB::raw('blocks'),
        ]);
    }

    public function down(): void
    {
        Schema::table('pb_pages', function (Blueprint $table) {
            $table->dropColumn('published_blocks');
        });
    }
};
