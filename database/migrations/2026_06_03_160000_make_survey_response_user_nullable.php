<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جعل survey_responses.user_id قابلاً لـ NULL لدعم إجابات الزوار على الاستبيانات العامة
 * (requires_login=false). كان NOT NULL فيفشل حفظ إجابة الزائر (Issue عالٍ).
 * قيد unique(survey_id,user_id) يقبل عدة NULL على InnoDB فلا يتعارض.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('survey_responses', 'user_id')) {
            Schema::table('survey_responses', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // لا نُرجِع NOT NULL لتفادي فشل الترحيل عند وجود صفوف زوّار (user_id=null).
    }
};
