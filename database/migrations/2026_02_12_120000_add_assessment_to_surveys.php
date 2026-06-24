<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            if (!Schema::hasColumn('surveys', 'survey_type')) {
                $table->string('survey_type')->default('general')->after('description');
                // general = استبيان عادي
                // pre_post_assessment = تقييم قبلي/بعدي
            }
            if (!Schema::hasColumn('surveys', 'lesson_id')) {
                $table->foreignId('lesson_id')->nullable()->after('survey_type')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('surveys', 'linked_survey_id')) {
                $table->foreignId('linked_survey_id')->nullable()->after('lesson_id');
                $table->foreign('linked_survey_id')->references('id')->on('surveys')->nullOnDelete();
            }
            if (!Schema::hasColumn('surveys', 'assessment_phase')) {
                $table->string('assessment_phase')->nullable()->after('linked_survey_id');
                // pre = قبلي, post = بعدي
            }
        });

        Schema::table('survey_responses', function (Blueprint $table) {
            if (!Schema::hasColumn('survey_responses', 'phase')) {
                $table->string('phase')->nullable()->after('answers');
                // pre = إجابة قبلية, post = إجابة بعدية
            }
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropForeign(['linked_survey_id']);
            $table->dropForeign(['lesson_id']);
            $table->dropColumn(['survey_type', 'lesson_id', 'linked_survey_id', 'assessment_phase']);
        });

        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn('phase');
        });
    }
};
