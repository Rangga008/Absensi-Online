<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

if (!function_exists('app_config')) {
    /**
     * Get application configuration with caching
     */
    function app_config($key = null, $default = null) {
        static $config = null;
        
        if ($config === null) {
            $config = [
                'app_name' => setting('app_name', 'Presensi Online'),
                'company_name' => setting('company_name', 'SMKN 2 Bandung'),
                'office_lat' => (float) setting('office_lat', -6.906000),
                'office_lng' => (float) setting('office_lng', 107.623400),
                'max_distance' => (int) setting('max_distance', 50000),
                'timezone' => setting('timezone', 'Asia/Jakarta'),
                'work_start_time' => setting('work_start_time', '07:00'),
                'work_end_time' => setting('work_end_time', '16:00'),
                'late_threshold' => setting('late_threshold', '08:00'),
                'logo' => setting('logo', null),
            ];
        }
        
        if ($key === null) {
            return $config;
        }
        
        return $config[$key] ?? $default;
    }
}

if (!function_exists('app_logo')) {
    /**
     * Get application logo URL
     */
    function app_logo() {
        try {
            $logo = setting('logo');
            
            if ($logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo)) {
                return asset('storage/' . $logo) . '?v=' . time();
            }

            return asset('images/logo-smk2.png') . '?v=' . time();
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in app_logo(): ' . $e->getMessage());
            return asset('images/logo-smk2.png') . '?v=' . time();
        }
    }
}

if (!function_exists('setting')) {
    /**
     * Get setting value with smart caching
     */
    function setting($key, $default = null) {
        try {
            // Don't cache logo to ensure fresh data
            if ($key === 'logo') {
                $setting = \App\Models\Setting::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            }
            
            // Use cache for other settings
            return \Illuminate\Support\Facades\Cache::remember("setting_{$key}", 3600, function() use ($key, $default) {
                $setting = \App\Models\Setting::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            });
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error getting setting {$key}: " . $e->getMessage());
            return $default;
        }
    }
}

if (!function_exists('office_location')) {
    /**
     * Get office location coordinates
     */
    function office_location() {
        return [
            'lat' => (float) setting('office_lat', -6.906000),
            'lng' => (float) setting('office_lng', 107.623400),
            'name' => setting('company_name', 'SMKN 2 Bandung'),
            'max_distance' => (int) setting('max_distance', 50000)
        ];
    }
}

if (!function_exists('work_hours')) {
    /**
     * Get work hours configuration
     */
    function work_hours() {
        return [
            'start' => setting('work_start_time', '07:00'),
            'end' => setting('work_end_time', '16:00'),
            'late_threshold' => setting('late_threshold', '08:00'),
            'timezone' => setting('timezone', 'Asia/Jakarta')
        ];
    }
}

if (!function_exists('is_within_work_hours')) {
    /**
     * Check if current time is within work hours
     */
    function is_within_work_hours($time = null) {
        $workHours = work_hours();
        $currentTime = $time ?? now()->setTimezone($workHours['timezone'])->format('H:i');
        
        return $currentTime >= $workHours['start'] && $currentTime <= $workHours['end'];
    }
}

if (!function_exists('is_late_attendance')) {
    /**
     * Check if attendance time is considered late
     */
    function is_late_attendance($time = null) {
        $workHours = work_hours();
        $currentTime = $time ?? now()->setTimezone($workHours['timezone'])->format('H:i');
        
        return $currentTime > $workHours['late_threshold'];
    }
}

if (!function_exists('clear_settings_cache')) {
    /**
     * Clear all settings cache
     */
    function clear_settings_cache() {
        $keys = [
            'app_name', 'company_name', 'office_lat', 'office_lng', 
            'max_distance', 'timezone', 'work_start_time', 
            'work_end_time', 'late_threshold', 'logo'
        ];
        
        foreach ($keys as $key) {
            \Illuminate\Support\Facades\Cache::forget("setting_{$key}");
        }
        
        // Also clear general cache
        \Illuminate\Support\Facades\Cache::forget('settings');
    }
}