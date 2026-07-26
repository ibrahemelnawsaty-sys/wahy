<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    protected $fillable = ['user_id', 'coins', 'reason', 'transaction_type', 'source', 'description'];

    /**
     * Defense-in-depth: coins are an append-only event log.
     * Block UPDATE and DELETE outside CLI (seeders / migrations).
     */
    protected static function booted(): void
    {
        static::updating(function (self $coin) {
            if (! app()->runningInConsole()) {
                abort(403, 'سجل العملات للقراءة فقط — لا يمكن تعديله');
            }
        });

        static::deleting(function (self $coin) {
            if (! app()->runningInConsole()) {
                abort(403, 'سجل العملات للقراءة فقط — لا يمكن حذفه');
            }
        });

        // إبطال كاش إحصائيات الطالب عند تغيّر العملات (تناظرٌ مع Point) — كان مفقوداً فتبقى
        // كويك-ستاتس/أيّ مستهلك مخزَّن متأخّراً عن رصيد العملات الحيّ.
        static::created(function (self $coin) {
            try {
                $userId = $coin->user_id;
                \Illuminate\Support\Facades\Cache::forget("student_stats_{$userId}");
                \Illuminate\Support\Facades\Cache::forget("student.quickstats.{$userId}");
            } catch (\Throwable $e) {
                // عدم كسر التدفّق لو فشل cache
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
