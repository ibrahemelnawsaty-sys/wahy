<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * تذاكر الدعم الفنيّ — كل مستخدم (أيّ دور) يرفع تذكرة، والدعم/السوبر أدمن يعالجها.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('support_tickets')) {
            return;
        }

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('subject');
            $table->text('message');
            $table->enum('category', ['technical', 'account', 'content', 'other'])->default('other');
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->enum('status', ['open', 'answered', 'resolved', 'closed'])->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('escalated')->default(false);
            $table->timestamp('escalated_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('last_reply_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
