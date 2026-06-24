<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إضافة أعمدة source/description المُتوقعة في كود GamificationService
     * إلى جداول points و coins، مع الحفاظ على عمود reason للتوافق العكسي.
     */
    public function up(): void
    {
        Schema::table('points', function (Blueprint $table) {
            if (!Schema::hasColumn('points', 'source')) {
                $table->string('source', 100)->nullable()->after('points');
            }
            if (!Schema::hasColumn('points', 'description')) {
                $table->string('description', 500)->nullable()->after('source');
            }
        });

        Schema::table('coins', function (Blueprint $table) {
            if (!Schema::hasColumn('coins', 'source')) {
                $table->string('source', 100)->nullable()->after('coins');
            }
            if (!Schema::hasColumn('coins', 'description')) {
                $table->string('description', 500)->nullable()->after('source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('points', function (Blueprint $table) {
            if (Schema::hasColumn('points', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('points', 'source')) {
                $table->dropColumn('source');
            }
        });

        Schema::table('coins', function (Blueprint $table) {
            if (Schema::hasColumn('coins', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('coins', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};
