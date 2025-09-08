    <?php

    if (!function_exists('app_logo')) {
        function app_logo() {
            try {
                // Clear any cache for logo setting
                $logo = setting('logo');
                
                // If logo setting exists and file actually exists
                if ($logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo)) {
                    return \Illuminate\Support\Facades\Storage::url($logo) . '?v=' . time();
                }


                // Fallback to default logo
                return asset('images/logo-smk2.png') . '?v=' . time();
                
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error in app_logo(): ' . $e->getMessage());
                return asset('images/logo-smk2.png') . '?v=' . time();
            }
        }
    }

    if (!function_exists('setting')) {
        function setting($key, $default = null) {
            try {
                // Don't use cache for logo to ensure fresh data
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