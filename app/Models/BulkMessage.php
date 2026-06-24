<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkMessage extends Model
{
    protected $fillable = [
        'sender_id',
        'recipient_type',
        'recipient_id',
        'school_id',
        'subject',
        'message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function recipients()
    {
        return $this->hasMany(BulkMessageRecipient::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get human-readable recipient type label
     */
    public function getRecipientTypeLabelAttribute(): string
    {
        $labels = [
            'teacher' => 'جميع المعلمين',
            'parent' => 'جميع أولياء الأمور',
            'student' => 'جميع الطلاب',
            'school_admin' => 'جميع مدراء المدارس',
            'all' => 'الجميع',
            'school_teachers' => 'معلمو مدرسة محددة',
            'school_parents' => 'أولياء أمور مدرسة محددة',
            'school_students' => 'طلاب مدرسة محددة',
            'school_all' => 'جميع منسوبي مدرسة محددة',
        ];

        return $labels[$this->recipient_type] ?? $this->recipient_type;
    }

    /**
     * Get badge color for recipient type
     */
    public function getRecipientTypeBadgeAttribute(): string
    {
        $badges = [
            'teacher' => 'primary',
            'parent' => 'success',
            'student' => 'warning',
            'school_admin' => 'info',
            'all' => 'danger',
            'school_teachers' => 'primary',
            'school_parents' => 'success',
            'school_students' => 'warning',
            'school_all' => 'info',
        ];

        return $badges[$this->recipient_type] ?? 'secondary';
    }
}
