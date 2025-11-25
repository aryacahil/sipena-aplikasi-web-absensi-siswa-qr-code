<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description'];

    /**
     * Get setting value by key
     */
    public static function get($key, $default = null)
    {
        // Cache untuk 1 jam
        $cacheKey = 'setting_' . $key;
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            // Parse value based on type
            return self::parseValue($setting->value, $setting->type);
        });
    }

    /**
     * Set setting value by key
     */
    public static function set($key, $value)
    {
        $setting = self::where('key', $key)->first();
        
        if ($setting) {
            $setting->value = self::formatValue($value, $setting->type);
            $setting->save();
        } else {
            self::create([
                'key' => $key,
                'value' => self::formatValue($value, 'string'),
                'type' => 'string'
            ]);
        }
        
        // Clear cache setelah update
        Cache::forget('setting_' . $key);
        
        return true;
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache()
    {
        // Get all setting keys
        $keys = self::pluck('key');
        
        foreach ($keys as $key) {
            Cache::forget('setting_' . $key);
        }
        
        // Also clear generic cache tags if using tagged cache
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags('settings')->flush();
        }
        
        return true;
    }

    /**
     * Parse value based on type
     */
    protected static function parseValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Format value based on type
     */
    protected static function formatValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'json':
                return json_encode($value);
            default:
                return (string) $value;
        }
    }
}