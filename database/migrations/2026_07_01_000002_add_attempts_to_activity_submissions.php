<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * عدّاد محاولات الطالب لكل نشاط — لتفعيل max_attempts (السماح بإعادة المحاولة حتى النجاح أو نفاد المحاولات).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('activity_submissions', 'attempts')) {
            Schema::table('activity_submissions', function (Blueprint $table) {
                $table->unsignedInteger('attempts')->default(1)->after('score')
                    ->comment('عدد محاولات الطالب لهذا النشاط');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('activity_submissions', 'attempts')) {
            Schema::table('activity_submissions', function (Blueprint $table) {
                $table->dropColumn('attempts');
            });
        }
    }
};
