<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\WorkTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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
            'kopsurat' => setting('kopsurat', null), // Added kopsurat setting
        ];

        // Get all work times for the work times management section
        $workTimes = WorkTime::with('users')->orderBy('name')->get();

        return view('admin.settings.index', compact('settings', 'workTimes'));
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
            'kopsurat' => 'nullable|image|mimes:png,ico,jpg,jpeg|max:2048', // Added validation for kopsurat
        ]);

        try {
            $logoUpdated = false;
            $kopsuratUpdated = false;

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');

                if (!$file->isValid()) {
                    return back()->with('error', 'File logo tidak valid')->withInput();
                }

                $oldLogo = setting('logo');

                $fileName = 'logo-' . time() . '.' . $file->getClientOriginalExtension();

                $logoDir = public_path('uploads/settings');
                if (!File::exists($logoDir)) {
                    File::makeDirectory($logoDir, 0755, true);
                }

                $file->move($logoDir, $fileName);
                $logoPath = 'uploads/settings/' . $fileName;

                if (!File::exists(public_path($logoPath))) {
                    throw new \Exception('File logo tidak ditemukan setelah upload');
                }

                Log::info('Saving new logo path: ' . $logoPath);
                Setting::setValue('logo', $logoPath);
                $logoUpdated = true;

                if ($oldLogo && $oldLogo !== $logoPath && File::exists(public_path($oldLogo))) {
                    File::delete(public_path($oldLogo));
                    Log::info('Deleted old logo: ' . $oldLogo);
                }
            }

            // Handle kopsurat upload
            if ($request->hasFile('kopsurat')) {
                $file = $request->file('kopsurat');

                if (!$file->isValid()) {
                    return back()->with('error', 'File kopsurat tidak valid')->withInput();
                }

                $oldKopsurat = setting('kopsurat');

                $fileName = 'kopsurat-' . time() . '.' . $file->getClientOriginalExtension();

                $kopsuratDir = public_path('uploads/settings');
                if (!File::exists($kopsuratDir)) {
                    File::makeDirectory($kopsuratDir, 0755, true);
                }

                $file->move($kopsuratDir, $fileName);
                $kopsuratPath = 'uploads/settings/' . $fileName;

                if (!File::exists(public_path($kopsuratPath))) {
                    throw new \Exception('File kopsurat tidak ditemukan setelah upload');
                }

                Log::info('Saving new kopsurat path: ' . $kopsuratPath);
                Setting::setValue('kopsurat', $kopsuratPath);
                $kopsuratUpdated = true;

                if ($oldKopsurat && $oldKopsurat !== $kopsuratPath && File::exists(public_path($oldKopsurat))) {
                    File::delete(public_path($oldKopsurat));
                    Log::info('Deleted old kopsurat: ' . $oldKopsurat);
                }
            }

            // Save other settings
            foreach ($validated as $key => $value) {
                if ($key !== 'logo' && $key !== 'kopsurat') {
                    Setting::setValue($key, $value);
                }
            }

            Cache::flush();
            Cache::forget('setting_logo');
            Cache::forget('setting_kopsurat');
            Cache::forget('settings');

            $message = 'Pengaturan berhasil diperbarui!';
            if ($logoUpdated) {
                $message .= ' Logo baru telah diupload dan akan diterapkan.';
            }
            if ($kopsuratUpdated) {
                $message .= ' Kopsurat baru telah diupload dan akan diterapkan.';
            }

            return redirect()->route('admin.settings.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error updating settings: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }
}
