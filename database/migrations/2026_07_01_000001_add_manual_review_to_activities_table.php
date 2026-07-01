<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إضافة مفتاح "يتطلب موافقة/تصحيح المعلم يدوياً" لكل نشاط.
     * عند تفعيله يذهب تسليم الطالب إلى حالة pending للمراجعة اليدوية
     * بدل التصحيح الآلي — بصرف النظر عن نوع النشاط.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('activities', 'manual_review')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->boolean('manual_review')->default(false)->after('passing_score')
                    ->comment('يتطلب موافقة/تصحيح المعلم يدوياً بدل التصحيح الآلي');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('activities', 'manual_review')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->dropColumn('manual_review');
            });
        }
    }
};
