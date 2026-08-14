<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * سجلّ كل بريد صادر (خطّة البريد P3) — يُملأ تلقائيًّا عبر أحداث Mail (MessageSending/MessageSent)
 * بلا تعديل أيّ Mailable. يقود لوحة المراقبة /admin/email-logs.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('email_logs')) {
            return;
        }

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('to_email')->index();
            $table->string('to_name')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();   // المستلِم إن كان مستخدمًا
            $table->string('subject')->nullable();
            $table->string('mailable_class')->nullable();
            $table->string('category')->default('transactional')->index(); // transactional|auth|campaign|digest|event
            $table->string('status')->default('sending')->index();         // sending|sent|failed|opened
            $table->text('error_message')->nullable();
            $table->longText('body')->nullable();                          // HTML للمعاينة/إعادة الإرسال
            $table->string('related_type')->nullable();                    // كيان مرتبط (نشاط/تذكرة/استبيان)
            $table->unsignedBigInteger('related_id')->nullable();
            $table->unsignedBigInteger('campaign_id')->nullable()->index();
            $table->unsignedBigInteger('sent_by')->nullable();             // الأدمن المُرسِل (للحملات)
            $table->string('message_id')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
