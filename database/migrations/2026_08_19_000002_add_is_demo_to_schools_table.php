<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * علم «مدرسة ديمو» — راحة: المستخدمون الجدد تحتها يرثون is_demo تلقائيّاً، وتبديلٌ جماعيّ،
 * واستبعاد المدرسة نفسها من صدارة/إحصاء المدارس. الحاسم في التجميعات يبقى users.is_demo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false)->index()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropIndex(['is_demo']);
            $table->dropColumn('is_demo');
        });
    }
};
