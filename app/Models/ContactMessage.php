<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'contact_messages';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'full_name',
        'email',
        'user_type',
        'message',
        'ip_address',
        'user_agent',
        'status',
        'replied_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user type in Arabic.
     */
    public function getUserTypeArabicAttribute()
    {
        $types = [
            'school' => 'مدرسة',
            'teacher' => 'معلم',
            'parent' => 'ولي أمر',
            'student' => 'طالب',
            'institution' => 'جهة تعليمية',
        ];

        return $types[$this->user_type] ?? $this->user_type;
    }

    /**
     * Scope to get unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    /**
     * Scope to get read messages.
     */
    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }
}
