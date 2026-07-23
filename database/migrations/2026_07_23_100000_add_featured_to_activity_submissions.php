<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * تمييز على مستوى **تسليم الطالب** (لا تعريف النشاط): يميّز المعلّم تسليماً متميّزاً لطالبٍ
 * فتستعرضه الإدارة في «الأنشطة المميّزة» للتقارير وتكريم الطلاب.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('activity_submissions', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('feedback');
            }
            if (! Schema::hasColumn('activity_submissions', 'featured_by')) {
                $table->unsignedBigInteger('featured_by')->nullable()->after('is_featured');
            }
            if (! Schema::hasColumn('activity_submissions', 'featured_at')) {
                $table->timestamp('featured_at')->nullable()->after('featured_by');
            }
            if (! Schema::hasColumn('activity_submissions', 'featured_reason')) {
                $table->string('featured_reason', 500)->nullable()->after('featured_at');
            }
        });

        if (Schema::hasColumn('activity_submissions', 'is_featured')) {
            Schema::table('activity_submissions', function (Blueprint $table) {
                $table->index('is_featured', 'activity_submissions_is_featured_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::table('activity_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('activity_submissions', 'is_featured')) {
                $table->dropIndex('activity_submissions_is_featured_idx');
            }
            foreach (['featured_reason', 'featured_at', 'featured_by', 'is_featured'] as $col) {
                if (Schema::hasColumn('activity_submissions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
