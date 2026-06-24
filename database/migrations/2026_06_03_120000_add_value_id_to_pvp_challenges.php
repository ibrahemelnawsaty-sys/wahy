<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pvp_challenges', function (Blueprint $table) {
            // ربط التحدي بقيمة (اختياري — null = تحدي عام لكل القيم)
            $table->foreignId('value_id')
                ->nullable()
                ->after('id')
                ->constrained('values')
                ->nullOnDelete();

            // اضافة عمود difficulty (كان مفقودًا من schema الأصلي)
            $table->string('difficulty', 20)
                ->default('medium')
                ->after('time_limit');

            $table->index(['value_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('pvp_challenges', function (Blueprint $table) {
            $table->dropIndex(['value_id', 'is_active']);
            $table->dropForeign(['value_id']);
            $table->dropColumn(['value_id', 'difficulty']);
        });
    }
};
