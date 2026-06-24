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
        Schema::create('values', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // الأمانة، التعاون، الإحسان...
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // emoji أو اسم أيقونة
            $table->integer('order')->default(0); // ترتيب العرض
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // السوبر أدمن
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('values');
    }
};
