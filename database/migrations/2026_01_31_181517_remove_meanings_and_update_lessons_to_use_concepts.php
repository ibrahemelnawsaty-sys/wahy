<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. إضافة عمود concept_id إلى lessons إذا لم يكن موجوداً
        if (! Schema::hasColumn('lessons', 'concept_id')) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->foreignId('concept_id')->nullable()->after('id');
            });
        }

        // 2. نقل البيانات من meanings إلى lessons (ربط lessons مباشرة بـ concepts)
        if (Schema::hasTable('meanings') && Schema::hasColumn('lessons', 'meaning_id')) {
            DB::statement('
                UPDATE lessons 
                SET concept_id = (
                    SELECT concept_id 
                    FROM meanings 
                    WHERE meanings.id = lessons.meaning_id
                )
                WHERE meaning_id IS NOT NULL
            ');
        }

        // 3. حذف العمود meaning_id من lessons (مع FK + indexes أولاً)
        if (Schema::hasColumn('lessons', 'meaning_id')) {
            // أولاً: حذف أي index يعتمد على meaning_id
            // (مثل idx_meaning_order من add_smart_performance_indexes)
            Schema::table('lessons', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_meaning_order');
                } catch (\Throwable $e) {
                    // غير موجود — تجاهل
                }
            });

            // ثانياً: حذف الـ FK constraint
            Schema::table('lessons', function (Blueprint $table) {
                try {
                    $table->dropForeign(['meaning_id']);
                } catch (\Throwable $e) {
                    // FK غير موجود — تجاهل
                }
            });

            // ثالثاً: حذف العمود
            Schema::table('lessons', function (Blueprint $table) {
                $table->dropColumn('meaning_id');
            });
        }

        // 4. Add foreign key for concept_id if not exists
        try {
            Schema::table('lessons', function (Blueprint $table) {
                $table->foreign('concept_id')
                    ->references('id')
                    ->on('concepts')
                    ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key already exists or concepts table doesn't exist
        }

        // 5. حذف جدول meanings
        Schema::dropIfExists('meanings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // إعادة إنشاء جدول meanings
        Schema::create('meanings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concept_id')->constrained('concepts')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // إعادة عمود meaning_id إلى lessons
        if (! Schema::hasColumn('lessons', 'meaning_id')) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->foreignId('meaning_id')->nullable()->after('concept_id')->constrained('meanings')->onDelete('cascade');
            });
        }

        // حذف عمود concept_id من lessons
        if (Schema::hasColumn('lessons', 'concept_id')) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->dropForeign(['concept_id']);
                $table->dropColumn('concept_id');
            });
        }
    }
};
