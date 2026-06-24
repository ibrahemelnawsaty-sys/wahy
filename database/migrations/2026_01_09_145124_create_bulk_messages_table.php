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
        Schema::create('bulk_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->enum('recipient_type', ['teacher', 'parent', 'student', 'all']);
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['recipient_type', 'sent_at']);
        });

        Schema::create('bulk_message_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bulk_message_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->foreign('bulk_message_id')->references('id')->on('bulk_messages')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_message_recipients');
        Schema::dropIfExists('bulk_messages');
    }
};
