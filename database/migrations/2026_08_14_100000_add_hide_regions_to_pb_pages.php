<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * دفعة 1 (خطّة محرّر الصفحات الاحترافيّ): تخصيص الهيدر/الفوتر لكلّ صفحة.
 * عمودان يفصلان دلالة «بلا هيدر/فوتر» عن دلالة null (= الافتراضيّ العالميّ):
 *   header_part_id/footer_part_id: null ⟶ الجزء الافتراضيّ العالميّ، أو id ⟶ جزء مُسمّى.
 *   hide_header/hide_footer: true ⟶ لا تُصيَّر المنطقة أصلاً لهذه الصفحة.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pb_pages', function (Blueprint $table) {
            $table->boolean('hide_header')->default(false)->after('footer_part_id');
            $table->boolean('hide_footer')->default(false)->after('hide_header');
        });
    }

    public function down(): void
    {
        Schema::table('pb_pages', function (Blueprint $table) {
            $table->dropColumn(['hide_header', 'hide_footer']);
        });
    }
};
