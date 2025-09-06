<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        try {
            // Basic counts
            $data = [
                'admin_name' => session('admin_name'),
                'admin_email' => session('admin_email'),
                'total_users' => \App\Models\User::count(),
                'total_roles' => \App\Models\Role::count(),
                'total_concessions' => \App\Models\Concession::count(),
                'total_salaries' => \App\Models\Salary::count(),
            ];

            // Attendance statistics
            $attendanceModel = \App\Models\Attendance::class;

            // Today's attendance
            $data['today_attendances'] = $attendanceModel::whereDate('present_date', today())->count();

            // This week's attendance
            $data['week_attendances'] = $attendanceModel::whereBetween('present_date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count();

            // This month's attendance
            $data['month_attendances'] = $attendanceModel::whereBetween('present_date', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])->count();

            // Total attendances
            $data['total_attendances'] = $attendanceModel::count();

            // Attendance by status
            $data['attendance_by_status'] = $attendanceModel::selectRaw('description, COUNT(*) as count')
                ->groupBy('description')
                ->get()
                ->pluck('count', 'description')
                ->toArray();

            // Recent attendances (last 10)
            $data['recent_attendances'] = $attendanceModel::with('user')
                ->orderBy('present_at', 'desc')
                ->limit(10)
                ->get();

            // Attendance trend for the last 7 days
            $data['attendance_trend'] = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $data['attendance_trend'][] = [
                    'date' => $date,
                    'count' => $attendanceModel::whereDate('present_date', $date)->count()
                ];
            }

            // Users with most attendances this month
            $data['top_users'] = \App\Models\User::withCount(['attendances' => function ($query) {
                $query->whereBetween('present_date', [now()->startOfMonth(), now()->endOfMonth()]);
            }])
            ->orderBy('attendances_count', 'desc')
            ->limit(5)
            ->get();

            // Attendance rate calculation
            $totalUsers = $data['total_users'];
            $thisMonthAttendances = $data['month_attendances'];
            $data['attendance_rate'] = $totalUsers > 0 ? round(($thisMonthAttendances / ($totalUsers * now()->daysInMonth)) * 100, 1) : 0;

            return view('admin.dashboard', $data);

        } catch (\Exception $e) {
            Log::error('Dashboard error', ['error' => $e->getMessage()]);

            // Fallback data
            return view('admin.dashboard', [
                'admin_name' => session('admin_name', 'Admin'),
                'admin_email' => session('admin_email', 'admin@example.com'),
                'total_users' => 0,
                'total_roles' => 0,
                'total_concessions' => 0,
                'total_salaries' => 0,
                'today_attendances' => 0,
                'week_attendances' => 0,
                'month_attendances' => 0,
                'total_attendances' => 0,
                'attendance_by_status' => [],
                'recent_attendances' => collect(),
                'attendance_trend' => [],
                'top_users' => collect(),
                'attendance_rate' => 0,
                'error' => 'Some data could not be loaded: ' . $e->getMessage()
            ]);
        }
    }
}