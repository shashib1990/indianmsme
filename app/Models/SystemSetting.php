<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value'
    ];

    /**
     * Helper to get a setting value with a default fallback
     */
    public static function getVal(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if ($setting && $setting->value !== null && trim($setting->value) !== '') {
            return $setting->value;
        }
        return $default;
    }

    /**
     * Helper to set a setting value
     */
    public static function setVal(string $key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );
    }
}
