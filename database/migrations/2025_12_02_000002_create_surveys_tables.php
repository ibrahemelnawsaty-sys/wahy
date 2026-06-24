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
        // إضافة الأعمدة الجديدة لجدول الاستبيانات الموجود
        if (Schema::hasTable('surveys')) {
            Schema::table('surveys', function (Blueprint $table) {
                if (!Schema::hasColumn('surveys', 'target_roles')) {
                    $table->json('target_roles')->nullable()->after('description');
                }
                if (!Schema::hasColumn('surveys', 'school_id')) {
                    $table->foreignId('school_id')->nullable()->after('target_roles')->constrained()->nullOnDelete();
                }
                if (!Schema::hasColumn('surveys', 'is_mandatory')) {
                    $table->boolean('is_mandatory')->default(true)->after('status');
                }
                if (!Schema::hasColumn('surveys', 'is_popup')) {
                    $table->boolean('is_popup')->default(true)->after('is_mandatory');
                }
                if (!Schema::hasColumn('surveys', 'start_date')) {
                    $table->timestamp('start_date')->nullable()->after('is_popup');
                }
                if (!Schema::hasColumn('surveys', 'end_date')) {
                    $table->timestamp('end_date')->nullable()->after('start_date');
                }
            });
        }
        
        if (!Schema::hasTable('surveys')) {
            // جدول الاستبيانات
            Schema::create('surveys', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->json('target_roles'); // ['student', 'teacher', 'parent', 'school_admin']
                $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete(); // null = لجميع المدارس
                $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
                $table->boolean('is_mandatory')->default(true); // إجباري
                $table->boolean('is_popup')->default(true); // يظهر كبوب أب
                $table->timestamp('start_date')->nullable();
                $table->timestamp('end_date')->nullable();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // جدول أسئلة الاستبيان
        if (!Schema::hasTable('survey_questions')) {
            Schema::create('survey_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
                $table->text('question_text');
                $table->enum('question_type', ['text', 'textarea', 'radio', 'checkbox', 'select', 'rating', 'scale']);
                $table->json('options')->nullable(); // الخيارات للأسئلة متعددة الخيارات
                $table->boolean('is_required')->default(true);
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        // إضافة الأعمدة الجديدة لجدول الإجابات الموجود
        if (Schema::hasTable('survey_responses')) {
            Schema::table('survey_responses', function (Blueprint $table) {
                if (!Schema::hasColumn('survey_responses', 'answers')) {
                    $table->json('answers')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('survey_responses', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable()->after('answers');
                }
            });
        } else {
            // جدول إجابات الاستبيان
            Schema::create('survey_responses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->json('answers'); // إجابات المستخدم
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->unique(['survey_id', 'user_id']); // كل مستخدم يجيب مرة واحدة فقط
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('surveys');
    }
};
