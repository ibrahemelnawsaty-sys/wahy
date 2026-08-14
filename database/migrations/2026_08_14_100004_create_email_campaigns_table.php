<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * حملات البريد الجماعيّة/المخصّصة (خطّة البريد P5). التتبّع لكل مستلِم عبر email_logs.campaign_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('email_campaigns')) {
            return;
        }

        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->longText('body');
            $table->string('audience_type')->default('all'); // all | role | school | custom
            $table->text('audience_value')->nullable();        // اسم الدور / معرّف المدرسة / قائمة بريد
            $table->string('status')->default('draft');         // draft | queued | sending | sent
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};
