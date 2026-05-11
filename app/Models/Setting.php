<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $guarded = [];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('system_settings'); 
        });
    }

    public static function getAllSettings()
    {
        return Cache::rememberForever('system_settings', function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    public static function get($key, $default = null)
    {
        $settings = self::getAllSettings();
        return $settings[$key] ?? $default;
    }
}
