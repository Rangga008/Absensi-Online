<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    public function index()
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login')->with('error', 'Please login as admin');
        }

        $settings = [
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

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login')->with('error', 'Please login as admin');
        }

        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'office_lat' => 'required|numeric|between:-90,90',
            'office_lng' => 'required|numeric|between:-180,180',
            'max_distance' => 'required|integer|min:1',
            'timezone' => 'required|string',
            'work_start_time' => 'required|date_format:H:i',
            'work_end_time' => 'required|date_format:H:i',
            'late_threshold' => 'required|date_format:H:i',
            'logo' => 'nullable|image|mimes:png,ico,jpg,jpeg|max:2048',
        ]);

        try {
            $logoUpdated = false;
            
            // Handle logo upload
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                
                if (!$file->isValid()) {
                    return back()->with('error', 'File logo tidak valid')->withInput();
                }

                // Get old logo path before uploading new one
                $oldLogo = setting('logo');
                
                // Generate unique filename
                $fileName = 'logo-' . time() . '.' . $file->getClientOriginalExtension();
                
                // Store in settings directory
                $logoPath = $file->storeAs('settings', $fileName, 'public');

                if (!$logoPath) {
                    throw new \Exception('Gagal menyimpan file logo');
                }

                // Verify file was actually stored
                if (!Storage::disk('public')->exists($logoPath)) {
                    throw new \Exception('File logo tidak ditemukan setelah upload');
                }

                // Save logo path to database
                Log::info('Saving new logo path: ' . $logoPath);
                Setting::setValue('logo', $logoPath);
                $logoUpdated = true;

                // Delete old logo if exists and different from new one
                if ($oldLogo && $oldLogo !== $logoPath && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                    Log::info('Deleted old logo: ' . $oldLogo);
                }
            }

            // Save other settings
            foreach ($validated as $key => $value) {
                if ($key !== 'logo') {
                    Setting::setValue($key, $value);
                }
            }

            // Clear all settings cache to force refresh
            Cache::flush();
            
            // Also clear specific cache keys
            Cache::forget('setting_logo');
            Cache::forget('settings');
            
            // Force clear Laravel config cache if it exists
            if (function_exists('config_clear')) {
                config_clear();
            }

            $message = 'Pengaturan berhasil diperbarui!';
            if ($logoUpdated) {
                $message .= ' Logo baru telah diupload dan akan diterapkan.';
            }

            return redirect()->route('admin.settings.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error updating settings: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }
}