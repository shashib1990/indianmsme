<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value'
    ];

    /**
     * In-memory request-level cache
     */
    protected static array $runtimeCache = [];

    protected const NOT_FOUND_SENTINEL = '__SYSTEM_SETTING_NOT_FOUND__';

    /**
     * Boot model events to automatically clear cache on save or delete
     */
    protected static function booted(): void
    {
        static::saved(function (self $setting) {
            static::clearCacheForKey($setting->key);
        });

        static::deleted(function (self $setting) {
            static::clearCacheForKey($setting->key);
        });
    }

    /**
     * Clear runtime and persistent cache for a specific key or all keys
     */
    public static function clearCacheForKey(?string $key = null): void
    {
        if ($key !== null) {
            unset(static::$runtimeCache[$key]);
            Cache::forget("sys_setting_{$key}");
        } else {
            static::$runtimeCache = [];
        }
    }

    /**
     * Helper to get a setting value with a default fallback.
     * Uses request-level memory cache first, then persistent cache, then DB.
     */
    public static function getVal(string $key, $default = null)
    {
        if (array_key_exists($key, static::$runtimeCache)) {
            $rawVal = static::$runtimeCache[$key];
        } else {
            $cacheKey = "sys_setting_{$key}";
            $rawVal = Cache::remember($cacheKey, 86400, function () use ($key) {
                $setting = self::where('key', $key)->first();
                if ($setting && $setting->value !== null) {
                    return (string) $setting->value;
                }
                return self::NOT_FOUND_SENTINEL;
            });
            static::$runtimeCache[$key] = $rawVal;
        }

        if ($rawVal !== self::NOT_FOUND_SENTINEL && $rawVal !== null && trim((string) $rawVal) !== '') {
            return $rawVal;
        }

        return $default;
    }

    /**
     * Helper to set a setting value and invalidate caches
     */
    public static function setVal(string $key, $value)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );

        static::clearCacheForKey($key);
        static::$runtimeCache[$key] = (string) $value;

        return $setting;
    }
}
