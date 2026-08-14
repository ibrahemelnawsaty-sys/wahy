<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * أنماط المستخدم القابلة لإعادة الاستخدام (تغذية راجعة): الأدمن يحفظ قسماً صمّمه لِيُدرِجه لاحقاً
 * في أيّ صفحة (كالووردبريس). الكتل تُصيَّر بالمُصيِّر الموثوق نفسه (قائمة سماح).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pb_user_patterns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('blocks');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pb_user_patterns');
    }
};
