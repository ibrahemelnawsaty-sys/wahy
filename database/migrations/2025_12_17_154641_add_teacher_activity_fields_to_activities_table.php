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
            $table->foreignId('created_by')->nullable()->after('lesson_id')->constrained('users')->onDelete('set null');
            $table->foreignId('classroom_id')->nullable()->after('created_by')->constrained('classrooms')->onDelete('cascade');
            $table->boolean('is_homework')->default(false)->after('type');
            $table->dateTime('due_date')->nullable()->after('is_homework');
            $table->string('attachment')->nullable()->after('questions');
            $table->integer('duration_minutes')->nullable()->after('passing_score')->comment('مدة النشاط بالدقائق');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['classroom_id']);
            $table->dropColumn(['created_by', 'classroom_id', 'is_homework', 'due_date', 'attachment', 'duration_minutes']);
        });
    }
};
