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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "المبتدئ الأمين"، "المتعاون النشط"...
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // رابط أو emoji
            $table->json('criteria')->nullable(); // شروط الحصول على الشارة (JSON)
            $table->enum('type', ['achievement', 'streak', 'special'])->default('achievement');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
