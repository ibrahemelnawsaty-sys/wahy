<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إنشاء جداول نظام التحدي (PvP) إن لم تكن موجودة.
 *
 * ملاحظة: كان migration إنشاء pvp_challenges مفقوداً من المستودع (موجود فقط
 * 2026_06_03_120000_add_value_id الذي يفترض وجود الجدول). هذا يجعل النشر الجديد
 * يفشل. هنا ننشئ البنية الأساسية بحماية hasTable حتى لا تتعارض مع بيئات الإنتاج
 * التي يوجد بها الجدولان فعلاً (تُتخطّى بأمان). الأعمدة value_id و difficulty
 * يضيفها migration 120000 اللاحق على pvp_challenges.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pvp_challenges')) {
            Schema::create('pvp_challenges', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->json('questions')->nullable();
                $table->integer('time_limit')->default(30);
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pvp_matches')) {
            Schema::create('pvp_matches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('challenge_id')->constrained('pvp_challenges')->cascadeOnDelete();
                $table->foreignId('player1_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('player2_id')->nullable()->constrained('users')->nullOnDelete();
                $table->json('player1_answers')->nullable();
                $table->json('player2_answers')->nullable();
                $table->integer('player1_score')->default(0);
                $table->integer('player2_score')->default(0);
                $table->integer('player1_time')->nullable();
                $table->integer('player2_time')->nullable();
                $table->foreignId('winner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status', 20)->default('waiting');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->index(['challenge_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        // لا نحذف الجداول في down لتفادي فقدان بيانات في الإنتاج؛ الإنشاء فقط idempotent.
    }
};
