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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meaning_id')->constrained('meanings')->onDelete('cascade');
            $table->string('title');
            $table->longText('content')->nullable(); // محتوى الدرس (نص، HTML...)
            $table->enum('type', ['video', 'text', 'interactive', 'mixed'])->default('text');
            $table->string('video_url')->nullable(); // رابط فيديو
            $table->string('audio_url')->nullable(); // رابط صوت
            $table->integer('duration')->default(5); // مدة الدرس بالدقائق
            $table->integer('points')->default(10); // النقاط عند إتمام الدرس
            $table->integer('order')->default(0);
            $table->enum('status', ['active', 'draft', 'archived'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
