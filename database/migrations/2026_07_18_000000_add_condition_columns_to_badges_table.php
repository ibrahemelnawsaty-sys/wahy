<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إضافة أعمدة قيادة المنح المبنيّ على الشرط لجدول الشارات.
     * آمنة (hasColumn) — نُبقي criteria/type القائمين؛ condition_type/value يقودان المنح.
     */
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            if (! Schema::hasColumn('badges', 'condition_type')) {
                // نوع الشرط: activities_completed | level | streak | points | lessons_completed | values_mastered
                $table->string('condition_type')->nullable()->after('criteria');
            }
            if (! Schema::hasColumn('badges', 'condition_value')) {
                $table->unsignedInteger('condition_value')->default(0)->after('condition_type');
            }
            if (! Schema::hasColumn('badges', 'coins_reward')) {
                $table->unsignedInteger('coins_reward')->default(50)->after('condition_value');
            }
            if (! Schema::hasColumn('badges', 'order')) {
                $table->integer('order')->default(0)->after('coins_reward');
            }
            if (! Schema::hasColumn('badges', 'color')) {
                $table->string('color')->nullable()->after('order'); // hex اختياري للعرض
            }
        });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            foreach (['condition_type', 'condition_value', 'coins_reward', 'order', 'color'] as $col) {
                if (Schema::hasColumn('badges', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
