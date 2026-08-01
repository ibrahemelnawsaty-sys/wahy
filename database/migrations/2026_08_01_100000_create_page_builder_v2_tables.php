<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * نموذج بيانات محرّر الصفحات الاحترافيّ (المرحلة 1) — شجرة كتل لكلّ لغة (ت-٣ نهج ب).
 * منفصل تماماً عن الأنظمة القديمة (page_builder / landing_content) — الترحيل بعلم ميزة لكلّ صفحة (ت-١٢).
 *
 * المبادئ: لا HTML خامّ (شجرة كتل مُتحقَّقة تُصيَّر عبر مكوّنات Blade)؛ مناطق مستقلّة (هيدر/فوتر/جسم)؛
 * إصدار لكلّ كتلة (ت-٢)؛ مسودّة/نشر + إصدارات (تراجع)؛ مكتبة وسائط (ت-٨).
 */
return new class extends Migration
{
    public function up(): void
    {
        // أجزاء القالب المشتركة (هيدر/فوتر) — مستقلّة وقابلة للتحرير كلٌّ على حدة، لكلّ لغة.
        Schema::create('pb_template_parts', function (Blueprint $table) {
            $table->id();
            $table->uuid('translation_group')->index();   // يربط الجزء نفسه عبر اللغات
            $table->string('locale', 8)->default('ar');
            $table->string('name');
            $table->string('kind', 20)->default('generic'); // header | footer | generic
            $table->json('blocks')->nullable();              // شجرة الكتل
            $table->boolean('is_active')->default(true);     // الجزء الفعّال لكلّ (kind, locale)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['kind', 'locale', 'is_active']);
        });

        Schema::create('pb_template_part_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_part_id')->constrained('pb_template_parts')->cascadeOnDelete();
            $table->json('blocks')->nullable();
            $table->string('label')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });

        // الصفحات — جسمها شجرة كتل خاصّة، وتشير لجزءَي هيدر/فوتر (أو الافتراضيّ).
        Schema::create('pb_pages', function (Blueprint $table) {
            $table->id();
            $table->uuid('translation_group')->index();   // يربط الصفحة نفسها عبر اللغات (ت-٣ نهج ب)
            $table->string('locale', 8)->default('ar');
            $table->string('title');
            $table->string('slug');
            $table->string('status', 20)->default('draft'); // draft | published
            $table->json('blocks')->nullable();              // شجرة كتل الجسم
            $table->foreignId('header_part_id')->nullable()->constrained('pb_template_parts')->nullOnDelete();
            $table->foreignId('footer_part_id')->nullable()->constrained('pb_template_parts')->nullOnDelete();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['slug', 'locale']);              // الـslug فريد ضمن اللغة
        });

        Schema::create('pb_page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pb_pages')->cascadeOnDelete();
            $table->json('blocks')->nullable();
            $table->string('label')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });

        // مكتبة الوسائط الموحّدة (ت-٨) — بديل الرفع المبعثر.
        Schema::create('pb_media', function (Blueprint $table) {
            $table->id();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt')->nullable();               // إلزاميّ منطقيّاً عند الإدراج (يُفرَض بالتطبيق)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pb_media');
        Schema::dropIfExists('pb_page_revisions');
        Schema::dropIfExists('pb_pages');
        Schema::dropIfExists('pb_template_part_revisions');
        Schema::dropIfExists('pb_template_parts');
    }
};
