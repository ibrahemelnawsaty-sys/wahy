<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ردّ على تذكرة دعم — من صاحب التذكرة أو من موظّف الدعم.
 */
class TicketReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'is_staff_reply',
    ];

    protected $casts = [
        'is_staff_reply' => 'boolean',
    ];

    /**
     * التذكرة التي ينتمي إليها الردّ.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    /**
     * كاتب الردّ.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
