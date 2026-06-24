<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * عرض رسالة — فقط المُرسِل والمُستقبِل (و super_admin للمراقبة).
     */
    public function view(User $user, Message $message): bool
    {
        return $user->role === 'super_admin'
            || $user->id === $message->sender_id
            || $user->id === $message->receiver_id;
    }

    public function create(User $user): bool
    {
        return true; // يحدّد التحقق من المُستقبِل في الـ controller
    }

    /**
     * تعديل — المُرسِل فقط (تعديل رسالة خاصة به).
     */
    public function update(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id;
    }

    /**
     * حذف — المُرسِل أو super_admin.
     */
    public function delete(User $user, Message $message): bool
    {
        return $user->role === 'super_admin'
            || $user->id === $message->sender_id;
    }

    /**
     * تحديد كمقروءة — المُستقبِل فقط.
     */
    public function markAsRead(User $user, Message $message): bool
    {
        return $user->id === $message->receiver_id;
    }
}
