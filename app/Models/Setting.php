<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description'];

    public static function get($key, $default = null)
    {
        $cacheKey = 'setting_' . $key;
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return self::parseValue($setting->value, $setting->type);
        });
    }

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
        
        Cache::forget('setting_' . $key);
        
        return true;
    }

    public static function clearCache()
    {
        $keys = self::pluck('key');
        
        foreach ($keys as $key) {
            Cache::forget('setting_' . $key);
        }
        
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags('settings')->flush();
        }
        
        return true;
    }

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