<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationRequest extends Model
{
    protected $fillable = [
        'user_id',
        'school_id',
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'data',
        'approved_by',
        'rejected_reason',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'data' => 'array',
    ];

    /**
     * المستخدم اللي قدم الطلب
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * المدرسة المطلوب الانضمام لها
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * المستخدم اللي وافق على الطلب
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
