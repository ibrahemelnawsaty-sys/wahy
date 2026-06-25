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
        Schema::create('user_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('shop_item_id')->constrained('shop_items')->onDelete('cascade');
            $table->integer('price_paid'); // السعر المدفوع
            $table->boolean('is_active')->default(true); // هل العنصر مفعل حالياً
            $table->dateTime('used_at')->nullable(); // تاريخ الاستخدام (للعناصر ذات الاستخدام الواحد)
            $table->timestamps();

            $table->index('user_id');
            $table->index('shop_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_purchases');
    }
};
