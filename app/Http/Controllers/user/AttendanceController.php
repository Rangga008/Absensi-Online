<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class AttendanceController extends Controller
{
    /**
     * Show attendance page
     */
    public function index()
     {
         return view('user.attendance');
     }   
     
     public function checkout()
   {
       return view('user.checkout');
   }

    /**
     * Process attendance submission
     */
    public function store(Request $request)
    {
        // Add detailed logging
        Log::info('Attendance submission attempt', [
            'user_id' => $request->get('user_id'),
            'description' => $request->get('description'),
            'has_photo' => !empty($request->get('photo')),
            'latitude' => $request->get('latitude'),
            'longitude' => $request->get('longitude'),
            'headers' => $request->headers->all()
        ]);
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'description' => 'required|in:Hadir,Terlambat,Sakit,Izin,Dinas Luar,WFH',
            'photo' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            // Get settings from database
            $officeLat = (float) setting('office_lat', -6.906000);
            $officeLng = (float) setting('office_lng', 107.623400);
            $maxDistance = (int) setting('max_distance', 500);
            $companyName = setting('company_name', 'sekolah');
            $timezone = setting('timezone', 'Asia/Jakarta');
            $currentTime = now($timezone);
            $workStartTime = setting('work_start_time', '07:00');
            $workEndTime = setting('work_end_time', '16:00');
            $lateThreshold = setting('late_threshold', '07:15');

            // Check for duplicate attendance first
            $this->checkDuplicateAttendance($validated['user_id'], $timezone);
            $presentDate = $currentTime->format('Y-m-d'); // Hanya tanggal

            // Process photo
            $photoPath = null;
            if (!empty($validated['photo'])) {
                $photoPath = $this->processPhoto($validated['photo'], $validated['user_id']);
            }

            // Calculate distance
            $distance = $this->calculateDistance(
                $validated['latitude'],
                $validated['longitude'],
                $officeLat,
                $officeLng
            );

            // Validate location for certain statuses
            $this->validateLocation($validated['description'], $distance, $maxDistance, $companyName);

            // Determine final status based on time
            $currentTime = now($timezone);
            $finalStatus = $this->determineAttendanceStatus(
                $validated['description'], 
                $currentTime,
                $workStartTime,
                $lateThreshold,
                $workEndTime
            );

            // Create attendance record
            $attendance = Attendance::create([
                'user_id' => $validated['user_id'],
                'description' => $finalStatus,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'photo_path' => $photoPath,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'distance' => $distance,
                'present_date' => $currentTime->format('Y-m-d'),
                'present_at' => $currentTime,
            ]);

            DB::commit();

            Log::info('Attendance created successfully', [
                'attendance_id' => $attendance->id,
                'user_id' => $validated['user_id'],
                'final_status' => $finalStatus,
                'distance' => $distance
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil dicatat dengan status: ' . $finalStatus,
                'data' => [
                    'time' => $attendance->present_at->format('H:i:s'),
                    'date' => $attendance->present_date,
                    'distance' => round($distance),
                    'status' => $finalStatus,
                    'photo_url' => $photoPath ? asset('storage/'.$photoPath) : null
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Attendance error: '.$e->getMessage(), [
                'user_id' => $validated['user_id'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Process and store photo
     */
    private function processPhoto($photoData, $userId)
    {
        try {
            // Remove data URL prefix if exists
            if (strpos($photoData, 'data:image') === 0) {
                $photoData = preg_replace('#^data:image/\w+;base64,#i', '', $photoData);
            }
            
            $photoData = str_replace(' ', '+', $photoData);
            $decodedImage = base64_decode($photoData);
            
            // Validate image
            if (!$decodedImage) {
                throw new \Exception('Invalid base64 image data');
            }
            
            // Test if it's a valid image
            $imageInfo = @getimagesizefromstring($decodedImage);
            if (!$imageInfo) {
                throw new \Exception('Invalid image format');
            }
            
            // Generate unique filename
            $imageName = 'attendance_'.time().'_'.$userId.'.jpg';
            $storagePath = 'attendance-photos/'.$imageName;
            
            // Store the image
            $stored = Storage::disk('public')->put($storagePath, $decodedImage);
            
            if (!$stored) {
                throw new \Exception('Failed to store image');
            }
            
            // Verify the file was actually created
            if (!Storage::disk('public')->exists($storagePath)) {
                throw new \Exception('Image file not found after storage');
            }
            
            Log::info('Photo processed successfully', [
                'path' => $storagePath,
                'size' => strlen($decodedImage),
                'user_id' => $userId
            ]);
            
            return $storagePath;
            
        } catch (\Exception $e) {
            Log::error('Photo processing failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw new \Exception('Gagal memproses foto: ' . $e->getMessage());
        }
    }

    /**
     * Check if user can attend today
     */
    public function checkStatus(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        
        $timezone = setting('timezone', 'Asia/Jakarta');
        $today = now($timezone)->format('Y-m-d');
        
        $attendance = Attendance::where('user_id', $request->user_id)
            ->whereDate('present_date', $today)
            ->first();

        return response()->json([
            'can_attend' => is_null($attendance),
            'can_checkout' => $attendance && !$attendance->hasCheckedOut(),
            'attendance' => $attendance ? $this->formatAttendanceData($attendance) : null,
            'server_time' => $this->currentServerTime($timezone),
            'force_check' => true
        ]);
    }

     public function checkCheckoutStatus(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        
        $timezone = setting('timezone', 'Asia/Jakarta');
        $today = now($timezone)->format('Y-m-d');
        
        $attendance = Attendance::where('user_id', $request->user_id)
            ->whereDate('present_date', $today)
            ->first();

       if (!$attendance) {
            return response()->json([
                'can_checkout' => false,
                'message' => 'Anda belum melakukan check-in hari ini',
                'server_time' => $this->currentServerTime($timezone)
            ]);
        }
        if ($attendance->hasCheckedOut()) {
            return response()->json([
                'can_checkout' => false,
                'message' => 'Anda sudah melakukan checkout pada pukul ' . $attendance->formatted_checkout_time,
                'attendance' => $this->formatAttendanceData($attendance),
                'server_time' => $this->currentServerTime($timezone)
            ]);
        }

        return response()->json([
            'can_checkout' => true,
            'attendance' => $this->formatAttendanceData($attendance),
           'server_time' => $this->currentServerTime($timezone)
        ]);
    }

    /**
     * Get attendance statistics
     */
    public function getStats(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2000'
        ]);

        $timezone = setting('timezone', 'Asia/Jakarta');
        $month = $request->month ?? now($timezone)->month;
        $year = $request->year ?? now($timezone)->year;

        $attendances = Attendance::where('user_id', $request->user_id)
            ->whereMonth('present_date', $month)
            ->whereYear('present_date', $year)
            ->get();

        $workingDays = $this->countWorkingDays($month, $year);
        $attendanceCount = $attendances->count();

        return response()->json([
            'stats' => [
                'working_days' => $workingDays,
                'present' => $attendances->whereIn('description', ['Hadir', 'Terlambat'])->count(),
                'late' => $attendances->where('description', 'Terlambat')->count(),
                'sick' => $attendances->where('description', 'Sakit')->count(),
                'permission' => $attendances->where('description', 'Izin')->count(),
                'business_trip' => $attendances->where('description', 'Dinas Luar')->count(),
                'wfh' => $attendances->where('description', 'WFH')->count(),
                'absent' => $workingDays - $attendanceCount,
                'attendance_rate' => $workingDays > 0 ? round(($attendanceCount / $workingDays) * 100, 2) : 0,
            ],
            'server_time' => $this->currentServerTime($timezone)
        ]);
    }

    /**
     * Check for duplicate attendance
     */
    private function checkDuplicateAttendance($userId, $timezone)
    {
        $today = now($timezone)->format('Y-m-d');
        $existing = Attendance::where('user_id', $userId)
            ->whereDate('present_date', $today)
            ->first();

        if ($existing) {
            throw new \Exception('Anda sudah melakukan absensi hari ini pada pukul ' . 
                                $existing->present_at->format('H:i:s') . 
                                ' dengan status: ' . $existing->description);
        }
    }

    /**
     * Determine attendance status based on time
     */
    private function determineAttendanceStatus($requestedStatus, Carbon $time, $workStartTime, $lateThreshold, $workEndTime)
    {
        // If status is not "Hadir", return as-is
        if ($requestedStatus !== 'Hadir') {
            return $requestedStatus;
        }

        // Parse time strings to Carbon objects
        $workStart = Carbon::createFromFormat('H:i', $workStartTime, $time->timezone);
        $lateThresholdTime = Carbon::createFromFormat('H:i', $lateThreshold, $time->timezone);
        $workEnd = Carbon::createFromFormat('H:i', $workEndTime, $time->timezone);
        
        // If before work start time, still allow but mark as early
        if ($time->lt($workStart)) {
            Log::info('Early attendance detected', [
                'time' => $time->format('H:i:s'),
                'work_start' => $workStart->format('H:i:s')
            ]);
            return 'Hadir'; // Still count as present
        }
        
        // If after work end time, mark as late attendance
        if ($time->gt($workEnd)) {
            return 'Terlambat';
        }
        
        // If between work start and late threshold, mark as present
        if ($time->lte($lateThresholdTime)) {
            return 'Hadir';
        }
        
        // Otherwise mark as late
        return 'Terlambat';
    }

    /**
     * Validate attendance location
     */
    private function validateLocation(string $status, float $distance, int $maxDistance, string $companyName): void
    {
        $exemptStatuses = ['WFH', 'Sakit', 'Izin', 'Dinas Luar'];
        
        if (in_array($status, $exemptStatuses)) {
            return; // No location validation needed
        }

        if ($distance > $maxDistance) {
            throw new \Exception(sprintf(
                'Jarak Anda %.0f meter dari %s (maksimal %.0f meter). Silakan mendekati lokasi untuk melakukan absensi.',
                $distance,
                $companyName,
                $maxDistance
            ));
        }
    }

    /**
     * Calculate distance using Haversine formula (in meters)
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meters

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat/2) * sin($deltaLat/2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLng/2) * sin($deltaLng/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Count working days (weekdays) in month
     */
    private function countWorkingDays(int $month, int $year): int
    {
        $timezone = setting('timezone', 'Asia/Jakarta');
        $date = Carbon::create($year, $month, 1, 0, 0, 0, $timezone);
        $days = 0;

        while ($date->month == $month) {
            if ($date->isWeekday()) {
                $days++;
            }
            $date->addDay();
        }

        return $days;
    }

    /**
     * Generate report summary
     */
    private function generateReportSummary(Collection $attendances): array
    {
        return [
            'total' => $attendances->count(),
            'present' => $attendances->where('description', 'Hadir')->count(),
            'late' => $attendances->where('description', 'Terlambat')->count(),
            'sick' => $attendances->where('description', 'Sakit')->count(),
            'permission' => $attendances->where('description', 'Izin')->count(),
            'business_trip' => $attendances->where('description', 'Dinas Luar')->count(),
            'wfh' => $attendances->where('description', 'WFH')->count()
        ];
    }

    /**
     * Format attendance data for response
     */
    private function formatAttendanceData(Attendance $attendance): array
    {
        $timezone = setting('timezone', 'Asia/Jakarta');
        $time = Carbon::parse($attendance->present_at)->setTimezone($timezone);
        
        return [
            'id' => $attendance->id,
            'time' => $time->format('H:i:s'),
            'date' => $time->format('d F Y'),
            'description' => $attendance->description,
            'coordinates' => [
                'latitude' => $attendance->latitude,
                'longitude' => $attendance->longitude
            ],
            'distance' => $attendance->distance ? round($attendance->distance) : null,
            'photo_url' => $attendance->photo_path ? asset('storage/'.$attendance->photo_path) : null,
            'has_checked_out' => $attendance->hasCheckedOut(),
        ];
         if ($attendance->hasCheckedOut()) {
            $checkoutTime = Carbon::parse($attendance->checkout_at)->setTimezone($timezone);
            $data['checkout'] = [
                'time' => $checkoutTime->format('H:i:s'),
                'coordinates' => [
                    'latitude' => $attendance->checkout_latitude,
                    'longitude' => $attendance->checkout_longitude
                ],
                'distance' => $attendance->checkout_distance ? round($attendance->checkout_distance) : null,
                'photo_url' => $attendance->checkout_photo_path ? asset('storage/'.$attendance->checkout_photo_path) : null,
                'work_duration' => $attendance->work_duration_formatted
            ];
        }
    }

    /**
     * Get current server time information
     */
    private function currentServerTime($timezone = 'Asia/Jakarta'): array
    {
        $now = now($timezone);
        return [
            'time' => $now->format('H:i:s'),
            'date' => $now->format('d F Y'),
            'day' => $now->translatedFormat('l'),
            'timezone' => $timezone,
            'timestamp' => $now->timestamp
        ];
    }

    /**
     * Clear user attendance cache
     */
    private function clearUserAttendanceCache($userId)
    {
        Cache::forget('user_attendance_'.$userId);
    }

    /**
     * Get user attendances (admin function)
     */
    public function userAttendances($userId)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }
        
        try {
            $user = User::findOrFail($userId);
            $attendances = Attendance::where('user_id', $userId)
                ->orderBy('present_at', 'desc')
                ->paginate(10);

            return view('admin.attendance.user_attendances', compact('user', 'attendances'));
        } catch (\Exception $e) {
            Log::error('Error loading user attendances', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error loading attendances: ' . $e->getMessage());
        }
    }

    private function checkCanCheckout($userId, $timezone)
    {
        $today = now($timezone)->format('Y-m-d');
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('present_date', $today)
            ->first();

        if (!$attendance) {
            throw new \Exception('Anda belum melakukan check-in hari ini. Silakan check-in terlebih dahulu.');
        }

        if ($attendance->hasCheckedOut()) {
          throw new \Exception('Anda sudah melakukan checkout hari ini pada pukul ' . 
                                $attendance->checkout_at->format('H:i:s'));
        }
       return $attendance;
   }

    /**
     * Process checkout submission
     */

    public function storeCheckout(Request $request)
    {
        Log::info('Checkout submission attempt', [
            'user_id' => $request->get('user_id'),
            'has_photo' => !empty($request->get('photo')),
            'latitude' => $request->get('latitude'),
            'longitude' => $request->get('longitude'),
      ]);
       
       $validated = $request->validate([
           'user_id' => 'required|exists:users,id',
           'latitude' => 'required|numeric|between:-90,90',
           'longitude' => 'required|numeric|between:-180,180',
            'photo' => 'required|string',
        ]);

        DB::beginTransaction();

       try {
           // Get settings from database
           $officeLat = (float) setting('office_lat', -6.906000);
            $officeLng = (float) setting('office_lng', 107.623400);
            $maxDistance = (int) setting('max_distance', 500);
            $companyName = setting('company_name', 'sekolah');
            $timezone = setting('timezone', 'Asia/Jakarta');

            // Check if user can checkout today
            $attendance = $this->checkCanCheckout($validated['user_id'], $timezone);

            // Process checkout photo
            $checkoutPhotoPath = null;
            if (!empty($validated['photo'])) {
                $checkoutPhotoPath = $this->processCheckoutPhoto($validated['photo'], $validated['user_id']);
            }

            // Calculate distance for checkout
            $checkoutDistance = $this->calculateDistance(
                $validated['latitude'],
                $validated['longitude'],
                $officeLat,
                $officeLng
            );

            $currentTime = now($timezone);
            
            // Calculate work duration
            $workDurationMinutes = $currentTime->diffInMinutes($attendance->present_at);

            Log::info('Work duration calculation', [
                'attendance_id' => $attendance->id,
                'present_at' => $attendance->present_at,
                'current_time' => $currentTime,
                'work_duration_minutes' => $workDurationMinutes
            ]);

            // Update attendance record with checkout data
            $updateData = [
                'checkout_at' => $currentTime,
                'checkout_latitude' => $validated['latitude'],
                'checkout_longitude' => $validated['longitude'],
                'checkout_photo_path' => $checkoutPhotoPath,
                'checkout_distance' => $checkoutDistance,
                'checkout_user_agent' => $request->userAgent(),
                'checkout_ip_address' => $request->ip(),
                'work_duration_minutes' => $workDurationMinutes,
            ];

            $updated = $attendance->update($updateData);

            Log::info('Attendance update result', [
                'attendance_id' => $attendance->id,
                'updated' => $updated,
                'work_duration_minutes_after_update' => $attendance->fresh()->work_duration_minutes
            ]);

           DB::commit();

           Log::info('Checkout recorded successfully', [
                'attendance_id' => $attendance->id,
                'user_id' => $validated['user_id'],
                'work_duration' => $workDurationMinutes,
                'checkout_distance' => $checkoutDistance
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Checkout berhasil dicatat',
              'data' => [
                   'checkout_time' => $attendance->checkout_at->format('H:i:s'),
                    'date' => $attendance->present_date,
                   'distance' => round($checkoutDistance),
                   'work_duration' => $attendance->work_duration_formatted,
                    'photo_url' => $checkoutPhotoPath ? asset('storage/'.$checkoutPhotoPath) : null
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Checkout error: '.$e->getMessage(), [
                'user_id' => $validated['user_id'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);
           
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
    private function processCheckoutPhoto($photoData, $userId)
    {
        try {
            // Remove data URL prefix if exists
            if (strpos($photoData, 'data:image') === 0) {
                $photoData = preg_replace('#^data:image/\w+;base64,#i', '', $photoData);
            }
            
           $photoData = str_replace(' ', '+', $photoData);
           $decodedImage = base64_decode($photoData);
            
            // Validate image
            if (!$decodedImage) {
                throw new \Exception('Invalid base64 image data');
            }
            
            // Test if it's a valid image
            $imageInfo = @getimagesizefromstring($decodedImage);
            if (!$imageInfo) {
                throw new \Exception('Invalid image format');
            }
            
            // Generate unique filename for checkout
            $imageName = 'checkout_'.time().'_'.$userId.'.jpg';
            $storagePath = 'attendance-photos/'.$imageName;
            
            // Store the image
            $stored = Storage::disk('public')->put($storagePath, $decodedImage);
           
            if (!$stored) {
               throw new \Exception('Failed to store checkout image');
            }
            
            // Verify the file was actually created
            if (!Storage::disk('public')->exists($storagePath)) {
                throw new \Exception('Checkout image file not found after storage');
           }
            
            Log::info('Checkout photo processed successfully', [
                'path' => $storagePath,
                'size' => strlen($decodedImage),
                'user_id' => $userId
            ]);
            
            return $storagePath;            
        } catch (\Exception $e) {
            Log::error('Checkout photo processing failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw new \Exception('Gagal memproses foto checkout: ' . $e->getMessage());
        }
    }
}