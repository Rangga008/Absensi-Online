<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Support\Facades\Cache; // Tambahkan ini
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    /**
     * Display attendance records (admin view)
     */
   public function index(Request $request)
{
    if (!session('is_admin')) {
        return redirect()->route('admin.login');
    }
    
    try {
        // Get roles excluding admin (id=1)
        $roles = Role::whereNotIn('id', [1]) // Exclude Super Admin
               ->whereNull('deleted_at')
               ->get();
        
        // Debug: tampilkan data roles
        // dd($roles);
        
        $query = User::withCount('attendances')
            ->with(['attendances' => function($query) {
                $query->orderBy('present_at', 'desc')->limit(1);
            }])
            ->where('role_id', '!=', 1); // Exclude admin

        if ($request->has('role_id') && $request->role_id != 'all') {
            $query->where('role_id', $request->role_id);
        }

        $users = $query->paginate(10);

        return view('admin.attendance.index', compact('users', 'roles'));
    } catch (\Exception $e) {
        Log::error('Error loading users attendance', ['error' => $e->getMessage()]);
        return back()->with('error', 'Error loading data: ' . $e->getMessage());
    }
}

    /**
     * Show attendance creation form (admin)
     */
    public function create()
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }
        
        try {
            $users = User::where('role_id', '!=', 1)->get(); // Exclude admin users
            return view('admin.attendance.create', compact('users'));
        } catch (\Exception $e) {
            Log::error('Error loading create form', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error loading form: ' . $e->getMessage());
        }
    }

    /**
     * Store new attendance record (admin)
     */
     public function store(Request $request)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'present_date' => 'required|date',  // Terima input date terpisah
            'present_time' => 'required',       // Terima input time terpisah
            'description' => 'required|in:Hadir,Terlambat,Sakit,Izin,Dinas Luar,WFH',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        try {
            // Gabungkan date dan time menjadi datetime
            $presentAt = Carbon::createFromFormat(
                'Y-m-d H:i', 
                $validated['present_date'] . ' ' . $validated['present_time']
            );

            Attendance::create([
                'user_id' => $validated['user_id'],
                'present_at' => $presentAt,
                'present_date' => $validated['present_date'],
                'description' => $validated['description'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return redirect()->route('admin.attendances.index')
                ->with('success', 'Absensi berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Error storing attendance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Gagal menyimpan absensi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show attendance details
     */
    public function show($id)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        try {
            $attendance = Attendance::with('user')->findOrFail($id);
            return view('admin.attendance.show', compact('attendance'));
        } catch (\Exception $e) {
            Log::error('Error showing attendance', ['error' => $e->getMessage(), 'id' => $id]);
            return back()->with('error', 'Attendance not found');
        }
    }

    /**
     * Show attendance edit form
     */
    public function edit($id)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        try {
            $attendance = Attendance::findOrFail($id);
            $users = User::where('role_id', '!=', 1)->get();
            return view('admin.attendance.edit', compact('attendance', 'users'));
        } catch (\Exception $e) {
            Log::error('Error loading edit form', ['error' => $e->getMessage(), 'id' => $id]);
            return back()->with('error', 'Attendance not found');
        }
    }

    /**
     * Update attendance record
     */
    public function update(Request $request, $id)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'present_date' => 'required|date',
            'present_time' => 'required',
            'description' => 'required|in:Hadir,Terlambat,Sakit,Izin,Dinas Luar,WFH',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        try {
            $attendance = Attendance::findOrFail($id);
            
            // Gabungkan date dan time menjadi datetime
            $presentAt = Carbon::createFromFormat(
                'Y-m-d H:i', 
                $validated['present_date'] . ' ' . $validated['present_time']
            );

            $attendance->update([
                'user_id' => $validated['user_id'],
                'present_at' => $presentAt,
                'present_date' => $validated['present_date'],
                'description' => $validated['description'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);

            return redirect()->route('admin.attendances.index')
                ->with('success', 'Absensi berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating attendance', [
                'error' => $e->getMessage(), 
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Gagal memperbarui absensi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete attendance record
     */
    public function destroy($id)
{
    try {
        $attendance = Attendance::findOrFail($id);
        $userId = $attendance->user_id;
        $attendance->delete();
        
        // Clear user's attendance cache
        $this->clearUserAttendanceCache($userId);
        
        return redirect()->route('admin.attendances.index')
            ->with('success', 'Absensi berhasil dihapus');
    } catch (\Exception $e) {
        Log::error('Error deleting attendance', ['error' => $e->getMessage(), 'id' => $id]);
        return back()->with('error', 'Error deleting attendance: ' . $e->getMessage());
    }
}

private function clearUserAttendanceCache($userId)
{
    // Clear server-side cache if any
    Cache::forget('user_attendance_'.$userId);
    
    // Anda juga bisa menambahkan log atau notifikasi ke user di sini
}

// Tambahkan method baru untuk menampilkan attendance per user


}