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

        // إبطال كاش لوحات الصدارة عند منح نقاط جديدة — عبر رفع «إصدار» يُبطل كل المفاتيح
        // القديمة فوراً (المفاتيح الفعلية تحوي md5 لمعاملاتها فيصعب حذفها فرادى؛ رفع الإصدار
        // يعمل مع أي مخزن كاش، بما فيه database).
        static::created(function (self $point) {
            try {
                $userId = $point->user_id;
                \Illuminate\Support\Facades\Cache::forever(
                    'lb:ver',
                    ((int) \Illuminate\Support\Facades\Cache::get('lb:ver', 1)) + 1,
                );
                // إحصائيات الطالب الفورية (هيدر/كويك-ستاتس) كي تُحدَّث النقاط مباشرة
                \Illuminate\Support\Facades\Cache::forget("student_stats_{$userId}");
                \Illuminate\Support\Facades\Cache::forget("student.quickstats.{$userId}");
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
