<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShopItem extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'price',
        'image',
        'icon',
        'stock',
        'is_limited',
        'available_until',
        'rarity',
        'metadata',
        'status',
        'order',
    ];

    protected $casts = [
        'is_limited' => 'boolean',
        'available_until' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * المستخدمين الذين اشتروا هذا العنصر
     */
    public function purchasers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_purchases')
            ->withPivot(['price_paid', 'is_active', 'used_at'])
            ->withTimestamps();
    }

    /**
     * هل العنصر متاح للشراء
     */
    public function isAvailable(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->stock !== null && $this->stock <= 0) {
            return false;
        }

        if ($this->is_limited && $this->available_until && $this->available_until->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * تقليل المخزون — atomic مع منع الرصيد السالب
     */
    public function decrementStock(): bool
    {
        if ($this->stock === null) {
            return true; // غير محدود
        }

        // تنفيذ شرطي ذرّي: نقصان فقط إذا كان stock > 0
        $affected = static::where('id', $this->id)
            ->where('stock', '>', 0)
            ->update([
                'stock' => \Illuminate\Support\Facades\DB::raw('stock - 1'),
            ]);

        if ($affected === 0) {
            return false; // نفد المخزون
        }

        // إعادة تحميل للتحقق من الرصيد بعد التحديث
        $this->refresh();

        if ($this->stock <= 0) {
            $this->update(['status' => 'sold_out']);
        }

        return true;
    }
}
