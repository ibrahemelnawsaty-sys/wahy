<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * علم «حساب ديمو» على المستخدم — المصدر الوحيد لاستثناء حسابات العرض من إحصاءات المنصّة.
 * default(false) → كل الحسابات القائمة تبقى حقيقيّة تلقائيّاً (لا تغيّر سلوكيّ حتى يُعلَّم حساب).
 * (خطّة docs/DEMO_ACCOUNTS_PLAN.md — الدفعة 0.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false)->index()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_demo']);
            $table->dropColumn('is_demo');
        });
    }
};
