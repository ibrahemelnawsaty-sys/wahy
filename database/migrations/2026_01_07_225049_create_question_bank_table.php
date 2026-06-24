<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('question_bank', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->onDelete('set null');
            $table->string('title');
            $table->text('question_text');
            $table->enum('question_type', ['multiple_choice', 'true_false', 'short_answer', 'essay'])->default('multiple_choice');
            $table->json('options')->nullable(); // للأسئلة متعددة الخيارات: [{'text': '...', 'is_correct': true/false}]
            $table->string('correct_answer')->nullable(); // للإجابات القصيرة
            $table->text('explanation')->nullable(); // شرح الإجابة الصحيحة
            $table->integer('points')->default(10); // نقاط السؤال
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // حالة الموافقة
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable(); // سبب الرفض
            $table->integer('usage_count')->default(0); // عدد مرات استخدام السؤال
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index('lesson_id');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_bank');
    }
};
