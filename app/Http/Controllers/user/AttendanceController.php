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
    // School coordinates (SMKN 2 Bandung)
    const SCHOOL_LATITUDE = -6.906000000000;
    const SCHOOL_LONGITUDE = 107.623400000000;
    const MAX_ALLOWED_DISTANCE = 50000; // 50km in meters
    
    // Work hours configuration (WIB timezone)
    const WORK_START_HOUR = 7;
    const WORK_END_HOUR = 17;
    const LATE_THRESHOLD_HOUR = 8;

    /**
     * Show attendance page
     */
    public function index()
    {
        return view('user.attendance');
    }   

    /**
     * Process attendance submission
     */
    public function store(Request $request)
    {
        // Add detailed logging
        Log::info('Attendance submission attempt', [
            'data' => $request->except('photo'), // Don't log photo data
            'headers' => $request->headers->all()
        ]);
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'description' => 'required|in:Hadir,Terlambat,Sakit,Izin,Dinas Luar,WFH',
            'photo' => 'required|string',
        ]);

        try {
            // Check for duplicate attendance first
            $this->checkDuplicateAttendance($validated['user_id']);

            // Process photo
            $photoPath = null;
            if ($validated['photo']) {
                $imageData = $validated['photo'];
                
                // Remove data URL prefix
                if (strpos($imageData, 'data:image') === 0) {
                    $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
                }
                
                $imageData = str_replace(' ', '+', $imageData);
                $decodedImage = base64_decode($imageData);
                
                // Validate image
                if (!$decodedImage || !@imagecreatefromstring($decodedImage)) {
                    throw new \Exception('Invalid image data');
                }
                
                $imageName = 'attendance_'.time().'_'.$validated['user_id'].'.jpg';
                $storagePath = 'attendance-photos/'.$imageName;
                
                // Store the image
                Storage::disk('public')->put($storagePath, $decodedImage);
                
                if (!Storage::disk('public')->exists($storagePath)) {
                    throw new \Exception('Failed to store image');
                }
                
                $photoPath = $storagePath;
            }

            // Calculate distance
            $distance = $this->calculateDistance(
                $validated['latitude'],
                $validated['longitude'],
                self::SCHOOL_LATITUDE,
                self::SCHOOL_LONGITUDE
            );

            // Create attendance record dengan semua field yang diperlukan
            $attendance = Attendance::create([
                'user_id' => $validated['user_id'],
                'description' => $validated['description'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'photo_path' => $photoPath,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'distance' => $distance,
                'present_date' => now('Asia/Jakarta')->format('Y-m-d'),
                'present_at' => now('Asia/Jakarta'),
            ]);

            Log::info('Attendance created successfully', [
                'attendance_id' => $attendance->id,
                'user_id' => $validated['user_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil dicatat',
                'data' => [
                    'time' => $attendance->present_at->format('H:i:s'),
                    'date' => $attendance->present_date,
                    'distance' => round($distance),
                    'photo_url' => $photoPath ? asset('storage/'.$photoPath) : null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Attendance error: '.$e->getMessage(), [
                'user_id' => $validated['user_id'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user can attend today
     */
    public function checkStatus(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        
        $today = now('Asia/Jakarta')->format('Y-m-d');
        $attendance = Attendance::where('user_id', $request->user_id)
            ->whereDate('present_date', $today)
            ->first();

        return response()->json([
            'can_attend' => is_null($attendance),
            'attendance' => $attendance ? $this->formatAttendanceData($attendance) : null,
            'server_time' => $this->currentServerTime(),
            'force_check' => true
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

        $month = $request->month ?? now('Asia/Jakarta')->month;
        $year = $request->year ?? now('Asia/Jakarta')->year;

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
            'server_time' => $this->currentServerTime()
        ]);
    }

    /**
     * Check for duplicate attendance
     */
    private function checkDuplicateAttendance($userId)
    {
        $today = now('Asia/Jakarta')->format('Y-m-d');
        $existing = Attendance::where('user_id', $userId)
            ->whereDate('present_date', $today)
            ->first();

        if ($existing) {
            throw new \Exception('Anda sudah melakukan absensi hari ini');
        }
    }

    /**
     * Determine attendance status based on time
     */
    private function determineAttendanceStatus($requestedStatus, Carbon $time)
    {
        // If status is not "Hadir", return as-is
        if ($requestedStatus !== 'Hadir') {
            return $requestedStatus;
        }

        $workStart = $time->copy()->setTime(self::WORK_START_HOUR, 0, 0);
        $lateThreshold = $time->copy()->setTime(self::LATE_THRESHOLD_HOUR, 0, 0);
        
        // If before work start time, mark as invalid (too early)
        if ($time->lt($workStart)) {
            throw new \Exception('Absensi terlalu awal. Jam kerja dimulai pukul '.self::WORK_START_HOUR.':00');
        }
        
        // If after work end time, mark as invalid (too late)
        if ($time->gt($time->copy()->setTime(self::WORK_END_HOUR, 0, 0))) {
            throw new \Exception('Absensi terlalu lambat. Jam kerja berakhir pukul '.self::WORK_END_HOUR.':00');
        }
        
        // If between work start and late threshold, mark as present
        if ($time->lte($lateThreshold)) {
            return 'Hadir';
        }
        
        // Otherwise mark as late
        return 'Terlambat';
    }

    /**
     * Validate attendance location
     */
    private function validateAttendanceLocation(string $status, ?float $latitude, ?float $longitude): ?float
    {
        $exemptStatuses = ['WFH', 'Sakit', 'Izin'];
        
        if (in_array($status, $exemptStatuses)) {
            return null;
        }

        if (is_null($latitude) || is_null($longitude)) {
            throw new \Exception('Lokasi tidak terdeteksi');
        }

        $distance = $this->calculateDistance(
            $latitude,
            $longitude,
            self::SCHOOL_LATITUDE,
            self::SCHOOL_LONGITUDE
        );

        if ($distance > self::MAX_ALLOWED_DISTANCE) {
            throw new \Exception(sprintf(
                'Jarak Anda %dm dari sekolah (maksimal %dm)',
                round($distance),
                self::MAX_ALLOWED_DISTANCE
            ));
        }

        return $distance;
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
        $date = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta');
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
        $time = Carbon::parse($attendance->present_at)->setTimezone('Asia/Jakarta');
        
        return [
            'id' => $attendance->id,
            'time' => $time->format('H:i:s'),
            'date' => $time->format('d F Y'),
            'description' => $attendance->description,
            'coordinates' => [
                'latitude' => $attendance->latitude,
                'longitude' => $attendance->longitude
            ]
        ];
    }

    /**
     * Get current server time information
     */
    private function currentServerTime(): array
    {
        $now = now('Asia/Jakarta');
        return [
            'time' => $now->format('H:i:s'),
            'date' => $now->format('d F Y'),
            'day' => $now->translatedFormat('l'),
            'timezone' => 'Asia/Jakarta (WIB)',
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
}