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
        Schema::create('content_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suggested_by')->constrained('users')->onDelete('cascade'); // المقترح
            $table->enum('type', ['value', 'concept', 'meaning', 'activity', 'lesson']); // نوع الاقتراح
            $table->string('title');
            $table->text('description');
            $table->json('metadata')->nullable(); // بيانات إضافية
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('review_notes')->nullable(); // ملاحظات المراجع
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_suggestions');
    }
};
