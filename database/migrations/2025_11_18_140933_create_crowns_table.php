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
        Schema::create('crowns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('value_id')->constrained('values')->onDelete('cascade');
            $table->timestamp('earned_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'value_id']); // تاج واحد لكل قيمة
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crowns');
    }
};
