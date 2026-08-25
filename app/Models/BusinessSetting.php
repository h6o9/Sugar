<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class BusinessSetting extends Model
{
    protected $guarded = [];

    public static function getValue(string $key, $default = null)
    {
        try {
            if (!Schema::hasTable('business_settings')) {
                return $default;
            }

            $settings = Cache::remember('business_settings_map', 60, function () {
                return static::query()->pluck('value', 'key')->toArray();
            });

            return array_key_exists($key, $settings) ? $settings[$key] : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function setValue(string $key, $value): void
    {
        if (!Schema::hasTable('business_settings')) {
            return;
        }

        static::updateOrCreate(['key' => $key], ['value' => is_array($value) ? json_encode($value) : $value]);
        Cache::forget('business_settings_map');
    }

    public static function json(string $key, $default = [])
    {
        $value = static::getValue($key);
        if ($value === null || $value === '') {
            return $default;
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : $default;
    }
}
