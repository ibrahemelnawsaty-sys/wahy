<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول pivot يربط المدرسة بالقيم المفعّلة لها.
     * - وجود صف لمدرسة معينة = هذه القيمة مفعّلة لها
     * - عدم وجود أي صفوف لمدرسة = جميع القيم مفعّلة افتراضياً (legacy compatibility)
     */
    public function up(): void
    {
        Schema::create('school_active_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('value_id')->constrained('values')->cascadeOnDelete();
            $table->foreignId('activated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('activated_at')->useCurrent();
            $table->timestamps();

            $table->unique(['school_id', 'value_id'], 'school_active_values_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_active_values');
    }
};
