<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * رسائل نموذج التواصل في صفحات المحرّر — تُخزَّن وتُشعِر الأدمن. عامّة الإرسال (محميّة
 * بـCSRF + throttle + honeypot). لا HTML (نصّ فقط مهرّب عند العرض في اللوحة).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pb_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('page_slug')->nullable();
            $table->string('name');
            $table->string('email');
            $table->text('message');
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pb_form_submissions');
    }
};
