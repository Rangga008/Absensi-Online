<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    // Koordinat SMKN 2 Bandung (sesuai dengan frontend)
    const OFFICE_LAT = -6.906000000000;
    const OFFICE_LNG = 107.623400000000;
    const MAX_DISTANCE = 50000; // maksimal 500 meter untuk toleransi GPS
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $attendances = Attendance::with('user')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.attendance.index', compact('attendances'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (session('role_id') == 3) {
            abort(404);
        }
        $users = User::all();
        return view('admin.attendance.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'present_at' => 'required|date',
                'description' => 'required|in:Hadir,Terlambat,Sakit,Izin,Dinas Luar,WFH',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ]);

            // Cek jika user sudah absen hari ini
            $today = Carbon::parse($request->present_at)->format('Y-m-d');
            $existingAttendance = Attendance::where('user_id', $request->user_id)
                ->whereDate('present_at', $today)
                ->first();

            if ($existingAttendance) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah melakukan absensi hari ini pada pukul ' . 
                                   Carbon::parse($existingAttendance->present_at)->format('H:i:s') . 
                                   ' dengan status: ' . $existingAttendance->description
                    ], 422);
                }
                return redirect()->back()->with('error', 'Anda sudah melakukan absensi hari ini!');
            }

            // Validasi lokasi (kecuali untuk WFH, Sakit, Izin)
            $exemptDescriptions = ['WFH', 'Sakit', 'Izin'];
            if (!in_array($request->description, $exemptDescriptions)) {
                if ($request->latitude && $request->longitude) {
                    $distance = $this->calculateDistance(
                        $request->latitude,
                        $request->longitude,
                        self::OFFICE_LAT,
                        self::OFFICE_LNG
                    );

                    if ($distance > self::MAX_DISTANCE) {
                        if ($request->ajax()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Anda berada terlalu jauh dari sekolah. Jarak Anda: ' . 
                                           round($distance) . ' meter dari SMKN 2 Bandung. Maksimal jarak: ' . 
                                           self::MAX_DISTANCE . ' meter.'
                            ], 422);
                        }
                        return redirect()->back()->with('error', 'Anda berada terlalu jauh dari sekolah untuk melakukan absensi.');
                    }
                } else {
                    // Jika tidak ada koordinat untuk status yang memerlukan lokasi
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Lokasi tidak terdeteksi. Pastikan GPS aktif dan izinkan akses lokasi.'
                        ], 422);
                    }
                    return redirect()->back()->with('error', 'Lokasi tidak terdeteksi untuk absensi.');
                }
            }

            // Buat record absensi
            $attendance = Attendance::create([
                'user_id' => $request->user_id,
                'present_at' => $request->present_at,
                'present_date' => Carbon::parse($request->present_at)->toDateString(), // Add this line
                'description' => $request->description,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            $user = User::find($request->user_id);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Absensi berhasil! Terima kasih ' . $user->name . '. Status: ' . $request->description,
                    'data' => [
                        'attendance_id' => $attendance->id,
                        'time' => Carbon::parse($request->present_at)->format('H:i:s'),
                        'date' => Carbon::parse($request->present_at)->format('d F Y'),
                        'description' => $request->description,
                        'distance' => isset($distance) ? round($distance) : null
                    ]
                ]);
            }

            return redirect('attendance')->with('message', 'Absensi baru telah ditambahkan!');
        } catch (\Exception $e) {
            \Log::error('Attendance submission error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);
        return view('admin.attendance.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (session('role_id') == 3) {
            abort(404);
        }
        $users = User::all();
        $attendance = Attendance::findOrFail($id);
        return view('admin.attendance.edit', compact('attendance', 'users'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (session('role_id') == 3) {
            abort(404);
        }
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'present_at' => 'required|date',
            'description' => 'required|in:Hadir,Terlambat,Sakit,Izin,Dinas Luar,WFH',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $user = User::find($request->user_id);

        $attendance = Attendance::findOrFail($id);
        $attendance->update([
            'user_id' => $request->user_id,
            'present_at' => $request->present_at,
            'description' => $request->description,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect('attendance')->with('message', 'Absensi dari <strong>' . $user->name . '</strong> telah diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (session('role_id') == 3) {
            abort(404);
        }
        
        $attendance = Attendance::findOrFail($id);
        $user = User::find($attendance->user_id);
        $attendance->delete();
        
        return redirect('attendance')->with('message', 'Absensi dari <strong>' . $user->name . '</strong> telah dihapus!');
    }

    /**
     * Check if user can attend today
     */
    public function checkAttendanceStatus(Request $request)
    {
        $userId = $request->user_id ?? session('user_id');
        $today = now()->format('Y-m-d');
        
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('present_at', $today)
            ->first();

        return response()->json([
            'can_attend' => !$attendance,
            'attendance' => $attendance ? [
                'id' => $attendance->id,
                'time' => Carbon::parse($attendance->present_at)->format('H:i:s'),
                'date' => Carbon::parse($attendance->present_at)->format('d F Y'),
                'description' => $attendance->description,
                'latitude' => $attendance->latitude,
                'longitude' => $attendance->longitude,
            ] : null
        ]);
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // Earth radius in meters

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLatRad = deg2rad($lat2 - $lat1);
        $deltaLngRad = deg2rad($lng2 - $lng1);

        $a = sin($deltaLatRad / 2) * sin($deltaLatRad / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLngRad / 2) * sin($deltaLngRad / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distance in meters
    }

    /**
     * Get attendance statistics
     */
    public function getStats(Request $request)
    {
        $userId = $request->user_id ?? session('user_id');
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $attendances = Attendance::where('user_id', $userId)
            ->whereMonth('present_at', $month)
            ->whereYear('present_at', $year)
            ->get();

        $totalWorkingDays = $this->getWorkingDaysInMonth($month, $year);

        $stats = [
            'total_days' => $totalWorkingDays,
            'total_attendance' => $attendances->count(),
            'hadir' => $attendances->where('description', 'Hadir')->count(),
            'terlambat' => $attendances->where('description', 'Terlambat')->count(),
            'sakit' => $attendances->where('description', 'Sakit')->count(),
            'izin' => $attendances->where('description', 'Izin')->count(),
            'dinas_luar' => $attendances->where('description', 'Dinas Luar')->count(),
            'wfh' => $attendances->where('description', 'WFH')->count(),
            'absent' => $totalWorkingDays - $attendances->count(),
            'attendance_rate' => $totalWorkingDays > 0 ? round(($attendances->count() / $totalWorkingDays) * 100, 2) : 0
        ];

        return response()->json($stats);
    }

    /**
     * Get working days in a month (excluding weekends)
     */
    private function getWorkingDaysInMonth($month, $year)
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $workingDays = 0;

        while ($startDate->lte($endDate)) {
            if ($startDate->isWeekday()) {
                $workingDays++;
            }
            $startDate->addDay();
        }

        return $workingDays;
    }

    /**
     * Get attendance report
     */
    public function getReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'nullable|exists:users,id'
        ]);

        $query = Attendance::with('user')
            ->whereBetween('present_at', [$request->start_date, $request->end_date]);

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $attendances = $query->orderBy('present_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $attendances,
            'summary' => [
                'total' => $attendances->count(),
                'hadir' => $attendances->where('description', 'Hadir')->count(),
                'terlambat' => $attendances->where('description', 'Terlambat')->count(),
                'sakit' => $attendances->where('description', 'Sakit')->count(),
                'izin' => $attendances->where('description', 'Izin')->count(),
                'dinas_luar' => $attendances->where('description', 'Dinas Luar')->count(),
                'wfh' => $attendances->where('description', 'WFH')->count(),
            ]
        ]);
    }
}