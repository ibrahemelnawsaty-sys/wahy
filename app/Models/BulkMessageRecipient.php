<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkMessageRecipient extends Model
{
    protected $fillable = [
        'bulk_message_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function bulkMessage()
    {
        return $this->belongsTo(BulkMessage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
