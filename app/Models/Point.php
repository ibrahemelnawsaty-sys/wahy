<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    protected $fillable = ['user_id', 'points', 'reason', 'source', 'description', 'activity_id', 'lesson_id'];

    /**
     * Defense-in-depth: points are an append-only event log.
     * Block UPDATE and DELETE outside CLI (seeders / migrations).
     */
    protected static function booted(): void
    {
        static::updating(function (self $point) {
            if (! app()->runningInConsole()) {
                abort(403, 'سجل النقاط للقراءة فقط — لا يمكن تعديله');
            }
        });

        static::deleting(function (self $point) {
            if (! app()->runningInConsole()) {
                abort(403, 'سجل النقاط للقراءة فقط — لا يمكن حذفه');
            }
        });

        // إبطال الـ leaderboard cache عند منح نقاط جديدة لمنع عرض ترتيب قديم
        static::created(function (self $point) {
            try {
                $userId = $point->user_id;
                \Illuminate\Support\Facades\Cache::forget('leaderboard:students:all');
                \Illuminate\Support\Facades\Cache::forget('leaderboard:students:week');
                \Illuminate\Support\Facades\Cache::forget('leaderboard:students:month');
                \Illuminate\Support\Facades\Cache::forget("lb:rank:student:{$userId}");
                \Illuminate\Support\Facades\Cache::forget("parent_dashboard:ranks:{$userId}");
            } catch (\Throwable $e) {
                // عدم كسر التدفق لو فشل cache
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
