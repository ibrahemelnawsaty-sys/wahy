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
        Schema::create('user_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('item_type'); // 'crown', 'streak_freeze', 'name_color', 'double_xp', etc.
            $table->json('item_data')->nullable(); // Extra data like color value, xp multiplier, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable(); // For time-limited items
            $table->timestamps();

            $table->index(['user_id', 'item_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_items');
    }
};
