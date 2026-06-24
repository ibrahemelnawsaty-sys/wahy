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
        Schema::create('shop_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['avatar', 'theme', 'badge', 'power_up', 'special'])->default('avatar');
            // avatar: صور رمزية
            // theme: ثيمات للواجهة
            // badge: شارات خاصة
            // power_up: تعزيزات (مثل مضاعفة النقاط)
            // special: عناصر خاصة
            $table->integer('price'); // السعر بالعملات
            $table->string('image')->nullable(); // صورة المنتج
            $table->string('icon')->default('🎁'); // أيقونة
            $table->integer('stock')->nullable(); // المخزون (null = غير محدود)
            $table->boolean('is_limited')->default(false); // هل محدود الوقت
            $table->dateTime('available_until')->nullable(); // متاح حتى
            $table->enum('rarity', ['common', 'rare', 'epic', 'legendary'])->default('common');
            // common: عادي
            // rare: نادر
            // epic: أسطوري
            // legendary: خرافي
            $table->json('metadata')->nullable(); // بيانات إضافية (مثل كود الثيم، رابط الصورة)
            $table->enum('status', ['active', 'inactive', 'sold_out'])->default('active');
            $table->integer('order')->default(0); // ترتيب العرض
            $table->timestamps();
            
            $table->index('type');
            $table->index('status');
            $table->index('rarity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_items');
    }
};
