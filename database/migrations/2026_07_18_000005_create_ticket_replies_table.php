<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ردود تذاكر الدعم — thread للتذكرة الواحدة (ردّ المالك وردّ الموظّف).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ticket_replies')) {
            return;
        }

        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->boolean('is_staff_reply')->default(false);
            $table->timestamps();

            $table->index('ticket_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_replies');
    }
};
