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
    
    $histories = DB::table('attendance')
        ->select('id', 'present_at', 'description', 'created_at')
        ->where('user_id', $userId)
        ->union(
            DB::table('concession')
                ->select('id', 'reason as present_at', 'description', 'created_at')
                ->where('user_id', $userId)
        )
        ->orderBy('created_at', 'desc')
        ->limit(7)
        ->get()
        ->map(function ($item) {
            $item->created_at = Carbon::parse($item->created_at);
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