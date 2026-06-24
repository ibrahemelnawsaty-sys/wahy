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
        Schema::table('activities', function (Blueprint $table) {
            // تحديث نوع النشاط ليشمل الأنواع الجديدة
            $table->enum('type', ['quiz', 'exercise', 'project', 'upload', 'practical', 'discussion'])->default('quiz')->change();
            
            // حقول خاصة بالاختبار
            $table->integer('quiz_duration')->nullable()->after('passing_score')->comment('مدة الاختبار بالدقائق');
            $table->integer('max_attempts')->nullable()->after('quiz_duration')->comment('عدد المحاولات المسموحة');
            
            // حقول خاصة بالمشروع
            $table->json('allowed_file_types')->nullable()->after('max_attempts')->comment('أنواع الملفات المسموحة: document, image, video, audio');
            $table->integer('max_file_size')->nullable()->after('allowed_file_types')->comment('الحد الأقصى لحجم الملف بالميغابايت');
            
            // تحديث status ليشمل draft
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->enum('type', ['quiz', 'upload', 'practical', 'discussion'])->default('quiz')->change();
            $table->dropColumn(['quiz_duration', 'max_attempts', 'allowed_file_types', 'max_file_size']);
            $table->enum('status', ['active', 'inactive'])->default('active')->change();
        });
    }
};
