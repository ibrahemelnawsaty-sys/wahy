<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description', 'user_id'];

    /**
     * Get setting value by key with optimized caching
     */
    public static function get($key, $default = null)
    {
        // استخدام Cache tags للتحكم الأفضل
        return Cache::remember("setting.{$key}", 86400, function () use ($key, $default) {
            $setting = self::where('key', $key)->first(['value', 'type']);
            
            if (!$setting) {
                return $default;
            }

            // تحويل القيمة حسب النوع
            return match ($setting->type) {
                'json' => json_decode($setting->value, true),
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'integer' => (int) $setting->value,
                'float' => (float) $setting->value,
                default => $setting->value,
            };
        });
    }

    /**
     * Get multiple settings at once (batch loading)
     */
    public static function getMany(array $keys, array $defaults = []): array
    {
        $result = [];
        $uncached = [];
        
        foreach ($keys as $key) {
            $cached = Cache::get("setting.{$key}");
            if ($cached !== null) {
                $result[$key] = $cached;
            } else {
                $uncached[] = $key;
            }
        }
        
        if (!empty($uncached)) {
            $settings = self::whereIn('key', $uncached)->get(['key', 'value', 'type']);
            
            foreach ($settings as $setting) {
                $value = match ($setting->type) {
                    'json' => json_decode($setting->value, true),
                    'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                    'integer' => (int) $setting->value,
                    'float' => (float) $setting->value,
                    default => $setting->value,
                };
                
                Cache::put("setting.{$setting->key}", $value, 86400);
                $result[$setting->key] = $value;
            }
            
            // إضافة القيم الافتراضية للمفاتيح غير الموجودة
            foreach ($uncached as $key) {
                if (!isset($result[$key])) {
                    $result[$key] = $defaults[$key] ?? null;
                }
            }
        }
        
        return $result;
    }

    /**
     * Set setting value
     */
    public static function set($key, $value, $type = 'string', $description = null)
    {
        // تحويل القيمة حسب النوع
        $storedValue = match ($type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'type' => $type,
                'description' => $description
            ]
        );

        // حذف من الكاش وإعادة تخزينه
        Cache::forget("setting.{$key}");
        Cache::put("setting.{$key}", $value, 86400);
        
        return $setting;
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache()
    {
        $keys = self::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }
    }
}
