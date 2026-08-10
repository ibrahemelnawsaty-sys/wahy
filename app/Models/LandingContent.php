<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class LandingContent extends Model
{
    protected $table = 'landing_content';

    protected $fillable = [
        'key',
        'value',
        'type',
        'section',
        'order',
        'metadata',
        'version',
        'updated_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'order' => 'integer',
        'version' => 'integer',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * جلب محتوى معين بالـ key
     */
    public static function getValue(string $key, $default = null)
    {
        $content = self::where('key', $key)->first();

        return $content ? $content->value : $default;
    }

    /**
     * تحديث أو إنشاء محتوى
     */
    public static function setValue(string $key, $value, array $options = [])
    {
        $row = self::updateOrCreate(
            ['key' => $key],
            array_merge([
                'value' => $value,
                'updated_by' => auth()->id(),
            ], $options),
        );

        self::flushMap(); // إبقاء كاش العرض متّسقاً مع أيّ كتابة

        return $row;
    }

    /**
     * خريطة key⟶value مُخبّأة (تقود العرض الخادميّ عبر lc()).
     * static داخل الطلب + كاش 10 دقائق عبر الطلبات؛ يُبطَل بأيّ كتابة أو flushMap().
     */
    protected static ?array $mapCache = null;

    public static function map(): array
    {
        if (self::$mapCache !== null) {
            return self::$mapCache;
        }
        try {
            self::$mapCache = \Illuminate\Support\Facades\Cache::remember('landing_content_map', 600, function () {
                return self::query()->pluck('value', 'key')->all();
            });
        } catch (\Throwable $e) {
            self::$mapCache = [];
        }

        return self::$mapCache;
    }

    public static function flushMap(): void
    {
        self::$mapCache = null;
        \Illuminate\Support\Facades\Cache::forget('landing_content_map');
    }

    /**
     * جلب كل محتوى قسم معين
     */
    public static function getSection(string $section)
    {
        return self::where('section', $section)
            ->orderBy('order')
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * حفظ نسخة احتياطية من المحتوى الحالي
     */
    public static function createSnapshot()
    {
        $content = self::all();

        // لا تحفظ نسخة احتياطية إذا كان الجدول فارغاً
        if ($content->isEmpty()) {
            return false;
        }

        DB::table('landing_content_versions')->insert([
            'content_snapshot' => json_encode($content),
            'created_by' => auth()->id(),
            'created_at' => now(),
        ]);

        return true;
    }
}
