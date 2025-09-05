<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    /**
     * Set a setting value
     */
    public static function setValue($key, $value)
    {
        try {
            $setting = self::where('key', $key)->first();
            
            if ($setting) {
                $setting->value = $value;
                $setting->save();
            } else {
                self::create([
                    'key' => $key,
                    'value' => $value
                ]);
            }
            
            // Clear cache for this setting
            Cache::forget("setting_{$key}");
            
            Log::info("Setting updated: {$key} = {$value}");
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to set setting {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get a setting value
     */
    public static function getValue($key, $default = null)
    {
        try {
            // Don't cache logo setting to ensure fresh data
            if ($key === 'logo') {
                $setting = self::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            }
            
            return Cache::remember("setting_{$key}", 3600, function() use ($key, $default) {
                $setting = self::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            });
            
        } catch (\Exception $e) {
            Log::error("Failed to get setting {$key}: " . $e->getMessage());
            return $default;
        }
    }
}