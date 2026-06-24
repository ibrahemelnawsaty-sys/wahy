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
            $table->enum('question_type', [
                'multiple_choice',      // اختيار متعدد (موجود)
                'true_false',          // صح/خطأ (موجود)
                'short_answer',        // إجابة قصيرة (موجود)
                'essay',               // مقالي (موجود)
                'letter_choice',       // اختيار حروف (جديد)
                'word_ordering',       // ترتيب كلمات (جديد)
                'sentence_ordering',   // ترتيب جمل (جديد)
                'image_ordering'       // ترتيب صور (جديد)
            ])->default('multiple_choice')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('question_type');
        });
    }
};
