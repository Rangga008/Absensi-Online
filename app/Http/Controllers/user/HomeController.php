<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Concession;
use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!session()->has('username')) {
                return redirect('/');
            }
            return $next($request);
        });
    }

    public function index()
    {
        return view('user.home');
    }

    public function about()
    {
        return view('user.about');
    }

    public function guide()
    {
        return view('user.guide');
    }

    public function concession()
    {
        return view('user.concession');
    }

    public function store_concession(Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'description' => 'required|string'
        ]);

        Concession::create([
            'user_id' => session('user_id'),
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'created_at' => Carbon::now(),
        ]);

        return redirect('user/home')->with('success', 'Permohonan izin berhasil diajukan!');
    }

    public function show_salary()
    {
        $salary_now = Salary::where('user_id', session('user_id'))
            ->latest()
            ->first();
            
        $salary_month = Salary::where('user_id', session('user_id'))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.view-salary', compact('salary_month', 'salary_now'));
    }

    public function show_history()
{
    $userId = session('user_id');

    // Query untuk attendance (kehadiran) dengan informasi checkout
    $attendance = DB::table('attendance')
        ->select('id', 'present_at', 'description', 'created_at', DB::raw("'hadir' as type"), DB::raw("NULL as status"),
                 'checkout_at', 'checkout_photo_path', 'work_duration_minutes', 'checkout_distance', 'photo_path')
        ->where('user_id', $userId);

    // Query untuk concession (izin) dengan status
    $concession = DB::table('concession')
        ->select('id', 'reason as present_at', 'description', 'created_at', DB::raw("'izin' as type"), 'status',
                 DB::raw("NULL as checkout_at"), DB::raw("NULL as checkout_photo_path"),
                 DB::raw("NULL as work_duration_minutes"), DB::raw("NULL as checkout_distance"), DB::raw("NULL as photo_path"))
        ->where('user_id', $userId)
        ->whereNull('deleted_at'); // Exclude soft-deleted records

    // Gabungkan kedua query
    $histories = $attendance->union($concession)
        ->orderBy('created_at', 'desc')
        ->limit(7)
        ->get()
        ->map(function ($item) {
            $item->created_at = Carbon::parse($item->created_at);
            if ($item->checkout_at) {
                $item->checkout_at = Carbon::parse($item->checkout_at);
            }
            // Only parse present_at for attendance records (type 'hadir'), for concession it's the reason
            if ($item->type === 'hadir' && $item->present_at) {
                $item->present_at = Carbon::parse($item->present_at);
            }

            // Calculate work_duration_formatted manually
            $minutes = $item->work_duration_minutes;
            if (!$minutes && $item->checkout_at && $item->present_at && $item->type === 'hadir') {
                $minutes = $item->checkout_at->diffInMinutes($item->present_at);
            }
            if ($minutes) {
                $hours = floor($minutes / 60);
                $remainingMinutes = $minutes % 60;
                $item->work_duration_formatted = sprintf('%d jam %d menit', $hours, $remainingMinutes);
            } else {
                $item->work_duration_formatted = '0 jam 0 menit';
            }

            return $item;
        });

    return view('user.history-attendance', compact('histories'));
}

    public function attendance()
    {
        return view('user.attendance');
    }

    public function do_attendance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $today = Carbon::today();
        $existingAttendance = Attendance::where('user_id', $request->user_id)
            ->whereDate('present_at', $today)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah absen hari ini!'
            ], 422);
        }

        Attendance::create([
            'user_id' => $request->user_id,
            'present_at' => Carbon::now(),
            'description' => 'Hadir',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih telah melakukan absensi!'
        ]);
    }
}