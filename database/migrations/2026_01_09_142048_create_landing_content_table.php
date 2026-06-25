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
        Schema::create('landing_content', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // hero_title, feature_1_title, etc.
            $table->text('value')->nullable(); // المحتوى
            $table->string('type')->default('text'); // text, image, html, json
            $table->string('section')->nullable(); // hero, features, cta, etc.
            $table->integer('order')->default(0); // ترتيب العناصر
            $table->json('metadata')->nullable(); // بيانات إضافية (css classes, attributes)
            $table->integer('version')->default(1); // رقم النسخة
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['section', 'order']);
        });

        // جدول للنسخ السابقة
        Schema::create('landing_content_versions', function (Blueprint $table) {
            $table->id();
            $table->json('content_snapshot'); // نسخة كاملة من المحتوى
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_content_versions');
        Schema::dropIfExists('landing_content');
    }
};
