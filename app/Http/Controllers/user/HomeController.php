<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Concession;
use App\Models\Salary;
use Carbon\Carbon;
use COM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Parent_;

class HomeController extends Controller
{

    public function index()
    {
        if (!session()->has('username')) {
            return back();
        }
        return view('user.home');
    }

    public function about()
    {
        if (!session()->has('username')) {
            return back();
        }
        return view('user.about');
    }

    public function guide()
    {
        if (!session()->has('username')) {
            return back();
        }
        return view('user.guide');
    }

    public function concession()
    {
        if (!session()->has('username')) {
            return back();
        }
        return view('user.concession');
    }

    public function store_concession(Request $request)
    {
        $request->validate([
            'description' => 'required'
        ]);

        Concession::create([
            'user_id' => session('user_id'),
            'reason' => $request->reason,
            'description' => $request->description,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect('user/home')->with('message', 'Concession has been created!');
    }

    public function show_salary()
    {
        $salary_now = Salary::where('user_id', session('user_id'))->first();
        $salary_month = Salary::where('user_id', session('user_id'))->get();
        return view('user.view-salary', compact('salary_month', 'salary_now'));
    }

    public function show_history()
    {
        // $history = Attendance::where('user_id', session('user_id'));

        $user_id = session('user_id');

        $histories = DB::select("SELECT id, present_at, description,created_at FROM attendance WHERE user_id = $user_id
        UNION
        SELECT id, reason, description, created_at FROM concession WHERE user_id = $user_id
        ORDER BY created_at DESC
        LIMIT 7");

        return view('user.history-attendance', compact('histories'));
    }

    public function attendance()
    {
        return view('user.attendance');
    }

    public function do_attendance(Request $request)
    {
        $attendance_status = Attendance::where('user_id', $request->user_id)->first();
        if ($attendance_status == null) {
            Attendance::create([
                'user_id' => $request->user_id,
                'present_at' => Carbon::now(),
                'description' => 'Hadir',
                'created_at' => Carbon::now(),
            ]);

            return response()->json([
                "user_id" => $request->user_id,
                "message" => "Thank you for your attendance!",
                "status"  => false
            ]);
        } else {
            $present = substr($attendance_status->present_at, 0, 10);
            $today = substr(today(), 0, 10);

            if ($present == $today) {
                return response([
                    'message'  => 'Anda sudah absen hari ini!',
                    'status'   => true
                ]);
            }
        }
    }
}
