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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['quiz', 'upload', 'practical', 'discussion'])->default('quiz');
            // quiz: اختبار متعدد الخيارات
            // upload: رفع صورة/فيديو (مثل السواك)
            // practical: نشاط عملي خارجي
            // discussion: مناقشة جماعية
            $table->json('questions')->nullable(); // أسئلة الاختبار بصيغة JSON
            $table->integer('points')->default(20);
            $table->integer('passing_score')->nullable(); // درجة النجاح (للاختبارات)
            $table->integer('order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
