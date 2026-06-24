<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user1_id',
        'user2_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * المستخدم الأول في المحادثة
     */
    public function user1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    /**
     * المستخدم الثاني في المحادثة
     */
    public function user2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    /**
     * جميع الرسائل في هذه المحادثة
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * آخر رسالة في المحادثة
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * الحصول على المستخدم الآخر في المحادثة
     */
    public function getOtherUser($userId)
    {
        return $this->user1_id == $userId ? $this->user2 : $this->user1;
    }

    /**
     * البحث عن محادثة بين مستخدمين أو إنشاؤها
     */
    public static function findOrCreate($user1Id, $user2Id)
    {
        // ترتيب الـ IDs لضمان عدم وجود محادثات مكررة
        $ids = [$user1Id, $user2Id];
        sort($ids);

        return self::firstOrCreate([
            'user1_id' => $ids[0],
            'user2_id' => $ids[1],
        ]);
    }

    /**
     * عدد الرسائل غير المقروءة للمستخدم
     */
    public function unreadCount($userId)
    {
        return $this->messages()
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();
    }
}
