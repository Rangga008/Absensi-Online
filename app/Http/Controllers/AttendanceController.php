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
        // Get all roles
        $roles = Role::whereNull('deleted_at')->get();
        
        $query = User::withCount('attendances')
            ->with(['latestAttendance'])
            ->with('role')
            ->when($request->has('role_id') && $request->role_id != 'all', function($q) use ($request) {
                $q->where('role_id', $request->role_id);
            })
            ->when($request->has('status') && $request->status != 'all', function($q) use ($request) {
                $q->whereHas('latestAttendance', function($query) use ($request) {
                    if ($request->status == 'present') {
                        $query->where('description', 'Hadir');
                    } elseif ($request->status == 'late') {
                        $query->where('description', 'Terlambat');
                    } elseif ($request->status == 'absent') {
                        $query->whereIn('description', ['Sakit', 'Izin']);
                    }
                });
            });
            
        // Handle sorting
        if ($request->has('sort')) {
            $sortColumn = $request->get('sort');
            $sortDirection = $request->get('direction', 'asc');
            
            if ($sortColumn === 'role') {
                $query->join('roles', 'users.role_id', '=', 'roles.id')
                      ->orderBy('roles.role_name', $sortDirection)
                      ->select('users.*');
            } 
            elseif ($sortColumn === 'last_attendance') {
                $query->leftJoin('attendances', function($join) {
                    $join->on('users.id', '=', 'attendances.user_id')
                         ->whereRaw('attendances.present_at = (SELECT MAX(present_at) FROM attendances WHERE attendances.user_id = users.id)');
                })
                ->orderBy('attendances.present_at', $sortDirection);
            }
            else {
                $query->orderBy($sortColumn, $sortDirection);
            }
        } else {
            // Default sorting
            $query->orderBy('name', 'asc');
        }

        $users = $query->paginate(10);

        return view('admin.attendance.index', compact('users', 'roles'));
    } catch (\Exception $e) {
        Log::error('Error loading users attendance', ['error' => $e->getMessage()]);
        return back()->with('error', 'Error loading data: ' . $e->getMessage());
    }
}

// Add this method to your AttendanceController
public function userAttendances(User $user)
{
    if (!session('is_admin')) {
        return redirect()->route('admin.login');
    }

    try {
        $attendances = $user->attendances()
            ->orderBy('present_at', 'desc')
            ->paginate(10); // Changed from get() to paginate(10)
            
        // Calculate distance for each attendance
        $attendances->getCollection()->transform(function($attendance) {
            if ($attendance->latitude && $attendance->longitude) {
                $attendance->distance = $this->calculateDistance(
                    $attendance->latitude,
                    $attendance->longitude,
                    -6.906000, // SMKN 2 Bandung latitude
                    107.623400 // SMKN 2 Bandung longitude
                );
            }
            return $attendance;
        });

        return view('admin.attendance.user', compact('user', 'attendances'));
    } catch (\Exception $e) {
        Log::error('Error loading user attendances', [
            'error' => $e->getMessage(),
            'user_id' => $user->id
        ]);
        return back()->with('error', 'Error loading attendance records');
    }
}

private function calculateDistance($lat1, $lon1, $lat2, $lon2)
{
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + 
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    return round($miles * 1609.344); // Convert to meters
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