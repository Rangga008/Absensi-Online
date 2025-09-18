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
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\AttendanceExport;

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

        // Get all users with their attendance data for client-side filtering
        $users = User::withCount('attendances')
            ->with(['latestAttendance'])
            ->with('role')
            ->orderBy('name', 'asc')
            ->paginate(50); // Increased pagination for better filtering experience

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
            ->paginate(10);
            
        // Get office location from settings
        $officeLat = (float) setting('office_lat', -6.906000);
        $officeLng = (float) setting('office_lng', 107.623400);
        
        // Calculate distance for each attendance
        $attendances->getCollection()->transform(function($attendance) use ($officeLat, $officeLng) {
            if ($attendance->latitude && $attendance->longitude) {
                $attendance->distance = $this->calculateDistance(
                    $attendance->latitude,
                    $attendance->longitude,
                    $officeLat,
                    $officeLng
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
    $earthRadius = 6371000; // Earth radius in meters
    
    // Convert from degrees to radians
    $latFrom = deg2rad($lat1);
    $lonFrom = deg2rad($lon1);
    $latTo = deg2rad($lat2);
    $lonTo = deg2rad($lon2);
    
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
    
    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    
    return round($angle * $earthRadius); // Distance in meters
}

    /**
     * Show attendance creation form (admin)
     */
    public function create(Request $request)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        try {
            // Get all users without role restrictions
            $users = User::orderBy('name')->get();

            // Get user_id from request if provided (for auto-selection)
            $selectedUserId = $request->get('user_id');

            return view('admin.attendance.create', compact('users', 'selectedUserId'));
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
    \Log::info('Attendance submission attempt', $request->all());

    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'description' => 'required|in:Hadir,Terlambat,Sakit,Izin',
        'photo' => 'required|string',
    ]);

    DB::beginTransaction();

    try {
        // Get office location from settings with fallback values
        $officeLat = (float) setting('office_lat', -6.906000);
        $officeLng = (float) setting('office_lng', 107.623400);
        $maxDistance = (int) setting('max_distance', 500);
        $companyName = setting('company_name', 'sekolah');

        // Calculate distance
        $distance = $this->calculateDistance(
            $validated['latitude'],
            $validated['longitude'],
            $officeLat,
            $officeLng
        );

        // Location validation
        if (in_array($validated['description'], ['Hadir', 'Terlambat']) && $distance > $maxDistance) {
            return response()->json([
                'success' => false,
                'message' => 'Anda berada di luar radius ' . $maxDistance . 'm dari ' . $companyName
            ], 400);
        }

        // Process photo
        $photoPath = null;
        if ($validated['photo']) {
            $image = $validated['photo'];
            
            if (strpos($image, 'data:image') === 0) {
                $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
            }
            
            $image = str_replace(' ', '+', $image);
            $imageData = base64_decode($image);
            
            if (!@imagecreatefromstring($imageData)) {
                throw new \Exception('Invalid image data');
            }
            
            $imageName = 'attendance_' . $validated['user_id'] . '_' . time() . '.jpg';
            $path = 'attendance-photos/' . $imageName;
            
            // Store the image
            $stored = Storage::disk('public')->put($path, $imageData);
            
            if (!$stored) {
                throw new \Exception('Failed to save image to storage');
            }
            
            $photoPath = $path;
            \Log::info('Photo saved', ['path' => $photoPath]);
        }

        // Create attendance record
        $attendance = Attendance::create([
            'user_id' => $validated['user_id'],
            'present_at' => now(),
            'present_date' => now()->format('Y-m-d'),
            'description' => $validated['description'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'photo_path' => $photoPath,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'distance' => $distance
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil dicatat',
            'data' => $attendance
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Attendance error', ['error' => $e->getMessage()]);
        
        // Delete the photo if it was saved but the transaction failed
        if (isset($photoPath) && Storage::disk('public')->exists($photoPath)) {
            Storage::disk('public')->delete($photoPath);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal menyimpan absensi: ' . $e->getMessage()
        ], 500);
    }
}


    /**
     * Show attendance details
     */
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
        
        // Get office location from settings
        $officeLat = (float) setting('office_lat', -6.906000);
        $officeLng = (float) setting('office_lng', 107.623400);
        
        // Calculate distance using settings
        if ($attendance->latitude && $attendance->longitude) {
            $attendance->distance = $this->calculateDistance(
                $attendance->latitude,
                $attendance->longitude,
                $officeLat,
                $officeLng
            );
        }

        if ($attendance->checkout_latitude && $attendance->checkout_longitude) {
            $attendance->checkout_distance = $this->calculateDistance(
                $attendance->checkout_latitude,
                $attendance->checkout_longitude,
                $officeLat,
                $officeLng
            );
       }
        
        // Get recent attendances for the same user (excluding current one)
        $recentAttendances = Attendance::where('user_id', $attendance->user_id)
            ->where('id', '!=', $id)
            ->orderBy('present_at', 'desc')
            ->limit(5)
            ->get();
            
        $photoPath = $attendance->photo_path;
        $exists = Storage::disk('public')->exists($photoPath);
        $fullPath = storage_path('app/public/'.$photoPath);
        
        Log::info('Photo debug', [
            'photo_path' => $photoPath,
            'exists' => $exists,
            'full_path' => $fullPath,
            'url' => Storage::url($photoPath)
        ]);

        return view('admin.attendance.show', compact('attendance', 'recentAttendances'));
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
        
        // Get office location from settings
        $officeLat = (float) setting('office_lat', -6.906000);
        $officeLng = (float) setting('office_lng', 107.623400);
        
        // Gabungkan date dan time menjadi datetime
        $presentAt = Carbon::createFromFormat(
            'Y-m-d H:i', 
            $validated['present_date'] . ' ' . $validated['present_time']
        );

        // Hitung ulang distance berdasarkan koordinat baru dan settings
        $distance = null;
        if ($validated['latitude'] && $validated['longitude']) {
            $distance = $this->calculateDistance(
                $validated['latitude'],
                $validated['longitude'],
                $officeLat,
                $officeLng
            );
        }

        $attendance->update([
            'user_id' => $validated['user_id'],
            'present_at' => $presentAt,
            'present_date' => $validated['present_date'],
            'description' => $validated['description'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'distance' => $distance, // Update distance yang baru
        ]);

        // Kembali ke halaman edit dengan pesan sukses
        return redirect()->route('admin.attendances.edit', $id)
            ->with('success', 'Data absensi berhasil diperbarui!');
            
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

        // Delete the photo if it exists
        if ($attendance->photo_path && Storage::disk('public')->exists($attendance->photo_path)) {
            Storage::disk('public')->delete($attendance->photo_path);
        }

        // Delete the checkout photo if it exists
        if ($attendance->checkout_photo_path && Storage::disk('public')->exists($attendance->checkout_photo_path)) {
            Storage::disk('public')->delete($attendance->checkout_photo_path);
        }

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

// Add this method to your AttendanceController
public function checkAttendanceStatus(Request $request)
{
    $userId = $request->input('user_id');
    
    $attendance = Attendance::where('user_id', $userId)
        ->whereDate('present_at', now())
        ->first();

    return response()->json([
        'can_attend' => is_null($attendance),
        'attendance' => $attendance ? [
            'time' => $attendance->present_at->format('H:i:s'),
            'description' => $attendance->description,
            'photo_url' => $attendance->photo_path ? asset('storage/'.$attendance->photo_path) : null
        ] : null
    ]);
}

// Tambahkan method baru untuk menampilkan attendance per user

    /**
     * Export attendance data to Excel
     */
    public function exportExcel(Request $request)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        try {
            $search = $request->get('search');
            $roleFilter = $request->get('role_id');
            $statusFilter = $request->get('status');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $query = Attendance::with(['user.role'])
                ->when($search, function($q) use ($search) {
                    $q->whereHas('user', function($query) use ($search) {
                        $query->where('name', 'like', "%$search%")
                              ->orWhereHas('role', function($q) use ($search) {
                                  $q->where('role_name', 'like', "%$search%");
                              });
                    });
                })
                ->when($roleFilter && $roleFilter != 'all', function($q) use ($roleFilter) {
                    $q->whereHas('user', function($query) use ($roleFilter) {
                        $query->where('role_id', $roleFilter);
                    });
                })
                ->when($statusFilter && $statusFilter != 'all', function($q) use ($statusFilter) {
                    if ($statusFilter == 'present') {
                        $q->where('description', 'Hadir');
                    } elseif ($statusFilter == 'late') {
                        $q->where('description', 'Terlambat');
                    } elseif ($statusFilter == 'absent') {
                        $q->whereIn('description', ['Sakit', 'Izin']);
                    } elseif ($statusFilter == 'other') {
                        $q->whereIn('description', ['Dinas Luar', 'WFH']);
                    }
                })
                ->when($startDate, function($q) use ($startDate) {
                    $q->whereDate('present_date', '>=', $startDate);
                })
                ->when($endDate, function($q) use ($endDate) {
                    $q->whereDate('present_date', '<=', $endDate);
                })
                ->orderBy('present_date', 'desc')
                ->orderBy('present_at', 'desc');

            $attendances = $query->get();

            $filename = 'attendance_export_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            return Excel::download(new class($attendances) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithMapping {
                private $attendances;

                public function __construct($attendances)
                {
                    $this->attendances = $attendances;
                }

                public function collection()
                {
                    return $this->attendances;
                }

                public function headings(): array
                {
                    return [
                        'Nama',
                        'Role',
                        'Tanggal',
                        'Waktu',
                        'Status',
                        'Checkout Status',
                        'Checkout Time',
                        'Latitude',
                        'Longitude',
                        'Jarak (m)',
                        'IP Address'
                    ];
                }

                public function map($attendance): array
                {
                    return [
                        $attendance->user->name ?? '',
                        $attendance->user->role->role_name ?? '',
                        $attendance->present_date,
                        $attendance->present_at ? $attendance->present_at->format('H:i:s') : '',
                        $attendance->description,
                        $attendance->hasCheckedOut() ? 'Sudah Keluar' : 'Belum Keluar',
                        $attendance->checkout_at ? $attendance->checkout_at->format('H:i:s') : '',
                        $attendance->latitude,
                        $attendance->longitude,
                        $attendance->distance,
                        $attendance->ip_address
                    ];
                }
            }, $filename);

        } catch (\Exception $e) {
            Log::error('Error exporting attendance to Excel', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error exporting data: ' . $e->getMessage());
        }
    }

    /**
     * Export attendance data to PDF
     */
    public function exportPdf(Request $request)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        try {
            $search = $request->get('search');
            $roleFilter = $request->get('role_id');
            $statusFilter = $request->get('status');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $query = Attendance::with(['user.role'])
                ->when($search, function($q) use ($search) {
                    $q->whereHas('user', function($query) use ($search) {
                        $query->where('name', 'like', "%$search%")
                              ->orWhereHas('role', function($q) use ($search) {
                                  $q->where('role_name', 'like', "%$search%");
                              });
                    });
                })
                ->when($roleFilter && $roleFilter != 'all', function($q) use ($roleFilter) {
                    $q->whereHas('user', function($query) use ($roleFilter) {
                        $query->where('role_id', $roleFilter);
                    });
                })
                ->when($statusFilter && $statusFilter != 'all', function($q) use ($statusFilter) {
                    if ($statusFilter == 'present') {
                        $q->where('description', 'Hadir');
                    } elseif ($statusFilter == 'late') {
                        $q->where('description', 'Terlambat');
                    } elseif ($statusFilter == 'absent') {
                        $q->whereIn('description', ['Sakit', 'Izin']);
                    } elseif ($statusFilter == 'other') {
                        $q->whereIn('description', ['Dinas Luar', 'WFH']);
                    }
                })
                ->when($startDate, function($q) use ($startDate) {
                    $q->whereDate('present_date', '>=', $startDate);
                })
                ->when($endDate, function($q) use ($endDate) {
                    $q->whereDate('present_date', '<=', $endDate);
                })
                ->orderBy('present_date', 'desc')
                ->orderBy('present_at', 'desc');

            $attendances = $query->get();

            $pdf = Pdf::loadView('admin.attendance.export_pdf', compact('attendances', 'search', 'roleFilter', 'statusFilter', 'startDate', 'endDate'));

            $filename = 'attendance_export_' . now()->format('Y-m-d_H-i-s') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Error exporting attendance to PDF', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error exporting data: ' . $e->getMessage());
        }
    }

    /**
     * Show export form
     */
    public function showExportForm()
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        try {
            $users = User::orderBy('name')->get(); // Show all users for export
            $roles = Role::whereNull('deleted_at')->orderBy('role_name')->get();

            return view('admin.attendance.export', compact('users', 'roles'));
        } catch (\Exception $e) {
            Log::error('Error loading export form', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error loading export form: ' . $e->getMessage());
        }
    }

    /**
     * Process export request
     */
    /**
 * Process export request
 */
/**
 * Process export request with better validation
 */
public function processExport(Request $request)
{
    $validated = $request->validate([
        'export_type' => 'required|in:by_date,by_user,by_role,by_date_range',
        'format' => 'required|in:excel,pdf',
        'specific_date' => 'nullable|date',
        'user_id' => 'nullable|exists:users,id',
        'role_id' => 'nullable|exists:roles,id',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date'
    ]);

    $query = Attendance::with('user.role');

    switch ($validated['export_type']) {
        case 'by_date':
            if (empty($validated['specific_date'])) {
                return back()->with('error', 'Specific date is required for export by date.');
            }
            $query->whereDate('present_date', $validated['specific_date']);
            break;
        case 'by_user':
            if (empty($validated['user_id'])) {
                return back()->with('error', 'User is required for export by user.');
            }
            $query->where('user_id', $validated['user_id']);
            break;
        case 'by_role':
            if (empty($validated['role_id'])) {
                return back()->with('error', 'Role is required for export by role.');
            }
            $query->whereHas('user', function($q) use ($validated) {
                $q->where('role_id', $validated['role_id']);
            });
            break;
        case 'by_date_range':
            if (empty($validated['start_date']) || empty($validated['end_date'])) {
                return back()->with('error', 'Start date and end date are required for export by date range.');
            }
            $query->whereBetween('present_date', [$validated['start_date'], $validated['end_date']]);
            break;
    }

    $attendances = $query->get();

    $filename = 'attendance_export_' . now()->format('Y-m-d_H-i-s');

    if ($validated['format'] === 'excel') {
        return Excel::download(new AttendanceExport($attendances), $filename . '.xlsx');
    } else {
        return $this->exportToPdf($attendances, $filename, $validated);
    }
}

    /**
     * Export user attendance summary
     */
    private function exportUserSummary($validated, $format)
    {
        $query = User::withCount('attendances')
            ->with(['role', 'latestAttendance'])
            ->where('role_id', '!=', 1); // Exclude admin

        if ($validated['summary_type'] === 'by_role' && isset($validated['summary_role_id'])) {
            $query->where('role_id', $validated['summary_role_id']);
        }

        $users = $query->orderBy('name')->get();

        $filename = 'attendance_summary_' . now()->format('Y-m-d_H-i-s');

        if ($format === 'excel') {
            return Excel::download(new class($users) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithMapping {
                private $users;

                public function __construct($users)
                {
                    $this->users = $users;
                }

                public function collection()
                {
                    return $this->users;
                }

                public function headings(): array
                {
                    return [
                        'User ID',
                        'Nama',
                        'Role',
                        'Total Hari Hadir',
                        'Terakhir Hadir'
                    ];
                }

                public function map($user): array
                {
                    return [
                        $user->id,
                        $user->name,
                        $user->role->role_name ?? '',
                        $user->attendances_count ?? 0,
                        $user->latestAttendance ? $user->latestAttendance->present_date : '-'
                    ];
                }
            }, $filename . '.xlsx');
        } else {
            $pdf = Pdf::loadView('admin.attendance.export_summary_pdf', compact('users', 'validated'));
            return $pdf->download($filename . '.pdf');
        }
    }

    /**
     * Export to Excel
     */
    private function exportToExcel($attendances, $filename)
    {
        return Excel::download(new class($attendances) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithMapping {
            private $attendances;

            public function __construct($attendances)
            {
                $this->attendances = $attendances;
            }

            public function collection()
            {
                return $this->attendances;
            }

            public function headings(): array
            {
                return [
                    'Nama',
                    'Role',
                    'Tanggal',
                    'Waktu',
                    'Status',
                    'Latitude',
                    'Longitude',
                    'Jarak (m)',
                    'IP Address'
                ];
            }

            public function map($attendance): array
            {
                return [
                    $attendance->user->name ?? '',
                    $attendance->user->role->role_name ?? '',
                    $attendance->present_date,
                    $attendance->present_at ? $attendance->present_at->format('H:i:s') : '',
                    $attendance->description,
                    $attendance->latitude,
                    $attendance->longitude,
                    $attendance->distance,
                    $attendance->ip_address
                ];
            }
        }, $filename . '.xlsx');
    }

    /**
     * Export to PDF
     */
    private function exportToPdf($attendances, $filename, $filters)
    {
        // Extract individual variables for the PDF view
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $search = $filters['search'] ?? null;
        $roleFilter = $filters['role_id'] ?? null;
        $statusFilter = $filters['status'] ?? null;

        $pdf = Pdf::loadView('admin.attendance.export_pdf', compact(
            'attendances',
            'startDate',
            'endDate',
            'search',
            'roleFilter',
            'statusFilter'
        ));
        return $pdf->download($filename . '.pdf');
    }

    /**
     * Export user attendance to PDF
     */
    public function exportUserPdf(User $user)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        try {
            $attendances = $user->attendances()
                ->orderBy('present_at', 'desc')
                ->get();

            // Add work duration and checkout status to each attendance
            $attendances->transform(function ($attendance) {
                $attendance->work_duration_formatted = $attendance->work_duration_formatted ?: '-';
                $attendance->checkout_status = $attendance->hasCheckedOut() ? 'Sudah Keluar' : 'Belum Keluar';
                $attendance->checkout_time_formatted = $attendance->checkout_at ? $attendance->checkout_at->format('H:i:s') : '-';
                return $attendance;
            });

            $pdf = Pdf::loadView('admin.attendance.export_user_pdf', compact('user', 'attendances'));

            $filename = 'attendance_' . $user->name . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Error exporting user attendance to PDF', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return back()->with('error', 'Error exporting data: ' . $e->getMessage());
        }
    }

    /**
     * Export user attendance to Excel
     */
    public function exportUserExcel(User $user)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        try {
            $attendances = $user->attendances()
                ->orderBy('present_at', 'desc')
                ->get();

            $filename = 'attendance_' . $user->name . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            return Excel::download(new class($attendances, $user) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithMapping {
                private $attendances;
                private $user;

                public function __construct($attendances, $user)
                {
                    $this->attendances = $attendances;
                    $this->user = $user;
                }

                public function collection()
                {
                    return $this->attendances;
                }

                public function headings(): array
                {
                    return [
                        'Tanggal',
                        'Waktu',
                        'Status',
                        'Checkout Status',
                        'Checkout Time',
                        'Durasi Kerja',
                        'Latitude',
                        'Longitude',
                        'Jarak (m)',
                        'IP Address',
                        'User Agent'
                    ];
                }

                public function map($attendance): array
                {
                    return [
                        $attendance->present_date,
                        $attendance->present_at ? $attendance->present_at->format('H:i:s') : '',
                        $attendance->description,
                        $attendance->hasCheckedOut() ? 'Sudah Keluar' : 'Belum Keluar',
                        $attendance->checkout_at ? $attendance->checkout_at->format('H:i:s') : '',
                        $attendance->work_duration_formatted ?: '-',
                        $attendance->latitude,
                        $attendance->longitude,
                        $attendance->distance,
                        $attendance->ip_address,
                        $attendance->user_agent
                    ];
                }
            }, $filename);

        } catch (\Exception $e) {
            Log::error('Error exporting user attendance to Excel', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return back()->with('error', 'Error exporting data: ' . $e->getMessage());
        }
    }

    /**
     * Get status filter value for client-side filtering
     */
    public static function getStatusFilterValue($description)
    {
        switch ($description) {
            case 'Hadir':
                return 'present';
            case 'Terlambat':
                return 'late';
            case 'Sakit':
            case 'Izin':
                return 'absent';
            case 'Dinas Luar':
            case 'WFH':
                return 'other';
            default:
                return 'no_record';
        }
    }

    /**
     * Process checkout for attendance (admin)
     */
    public function checkout(Request $request, $id)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        $validated = $request->validate([
            'checkout_at' => 'required|date',
            'checkout_time' => 'required',
            'checkout_latitude' => 'nullable|numeric|between:-90,90',
            'checkout_longitude' => 'nullable|numeric|between:-180,180',
            'checkout_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $attendance = Attendance::findOrFail($id);

            if ($attendance->hasCheckedOut()) {
                return back()->with('error', 'Attendance already has checkout recorded.');
            }

            // Combine date and time
            $checkoutAt = Carbon::createFromFormat(
                'Y-m-d H:i',
                $validated['checkout_at'] . ' ' . $validated['checkout_time']
            );

            // Calculate work duration
            $workDurationMinutes = $checkoutAt->diffInMinutes($attendance->present_at);

            // Process checkout photo if provided
            $checkoutPhotoPath = null;
            if ($request->hasFile('checkout_photo')) {
                $checkoutPhotoPath = $this->processCheckoutPhotoFile($request->file('checkout_photo'), $attendance->user_id);
            }

            // Calculate checkout distance if coordinates provided
            $checkoutDistance = null;
            if ($validated['checkout_latitude'] && $validated['checkout_longitude']) {
                $officeLat = (float) setting('office_lat', -6.906000);
                $officeLng = (float) setting('office_lng', 107.623400);
                $checkoutDistance = $this->calculateDistance(
                    $validated['checkout_latitude'],
                    $validated['checkout_longitude'],
                    $officeLat,
                    $officeLng
                );
            }

            $attendance->update([
                'checkout_at' => $checkoutAt,
                'checkout_latitude' => $validated['checkout_latitude'],
                'checkout_longitude' => $validated['checkout_longitude'],
                'checkout_photo_path' => $checkoutPhotoPath,
                'checkout_distance' => $checkoutDistance,
                'work_duration_minutes' => $workDurationMinutes,
            ]);

            return redirect()->route('admin.attendances.show', $id)
                ->with('success', 'Checkout recorded successfully.');

        } catch (\Exception $e) {
            Log::error('Error recording checkout', ['error' => $e->getMessage(), 'id' => $id]);
            return back()->with('error', 'Error recording checkout: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Process checkout photo for admin (file upload)
     */
    private function processCheckoutPhotoFile($photoFile, $userId)
    {
        try {
            // Validate file
            if (!$photoFile->isValid()) {
                throw new \Exception('Invalid file upload');
            }

            // Generate unique filename for checkout
            $extension = $photoFile->getClientOriginalExtension();
            $imageName = 'checkout_admin_' . time() . '_' . $userId . '.' . $extension;
            $storagePath = 'attendance-photos/' . $imageName;

            // Store the image
            $stored = Storage::disk('public')->put($storagePath, file_get_contents($photoFile->getRealPath()));

            if (!$stored) {
                throw new \Exception('Failed to store checkout image');
            }

            // Verify the file was actually created
            if (!Storage::disk('public')->exists($storagePath)) {
                throw new \Exception('Checkout image file not found after storage');
            }

            Log::info('Admin checkout photo processed successfully', [
                'path' => $storagePath,
                'size' => $photoFile->getSize(),
                'user_id' => $userId
            ]);

            return $storagePath;

        } catch (\Exception $e) {
            Log::error('Admin checkout photo processing failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw new \Exception('Failed to process checkout photo: ' . $e->getMessage());
        }
    }

    /**
     * Export single attendance to PDF
     */
    public function exportSinglePdf($id)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        try {
            $attendance = Attendance::with('user')->findOrFail($id);

            // Get office location from settings
            $officeLat = (float) setting('office_lat', -6.906000);
            $officeLng = (float) setting('office_lng', 107.623400);

            // Calculate distances
            if ($attendance->latitude && $attendance->longitude) {
                $attendance->distance = $this->calculateDistance(
                    $attendance->latitude,
                    $attendance->longitude,
                    $officeLat,
                    $officeLng
                );
            }

            if ($attendance->checkout_latitude && $attendance->checkout_longitude) {
                $attendance->checkout_distance = $this->calculateDistance(
                    $attendance->checkout_latitude,
                    $attendance->checkout_longitude,
                    $officeLat,
                    $officeLng
                );
            }

            $pdf = Pdf::loadView('admin.attendance.export_single_pdf', compact('attendance'));

            $filename = 'attendance_' . $attendance->user->name . '_' . $attendance->present_date . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Error exporting single attendance to PDF', ['error' => $e->getMessage(), 'id' => $id]);
            return back()->with('error', 'Error exporting data: ' . $e->getMessage());
        }
    }

    /**
     * Export single attendance to Excel
     */
    public function exportSingleExcel($id)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        try {
            $attendance = Attendance::with('user')->findOrFail($id);

            // Get office location from settings
            $officeLat = (float) setting('office_lat', -6.906000);
            $officeLng = (float) setting('office_lng', 107.623400);

            // Calculate distances
            if ($attendance->latitude && $attendance->longitude) {
                $attendance->distance = $this->calculateDistance(
                    $attendance->latitude,
                    $attendance->longitude,
                    $officeLat,
                    $officeLng
                );
            }

            if ($attendance->checkout_latitude && $attendance->checkout_longitude) {
                $attendance->checkout_distance = $this->calculateDistance(
                    $attendance->checkout_latitude,
                    $attendance->checkout_longitude,
                    $officeLat,
                    $officeLng
                );
            }

            $filename = 'attendance_' . $attendance->user->name . '_' . $attendance->present_date . '.xlsx';

            return Excel::download(new class([$attendance]) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithMapping {
                private $attendances;

                public function __construct($attendances)
                {
                    $this->attendances = $attendances;
                }

                public function collection()
                {
                    return collect($this->attendances);
                }

                public function headings(): array
                {
                    return [
                        'Nama',
                        'Tanggal',
                        'Waktu Masuk',
                        'Status',
                        'Waktu Keluar',
                        'Durasi Kerja',
                        'Latitude Masuk',
                        'Longitude Masuk',
                        'Jarak Masuk (m)',
                        'Latitude Keluar',
                        'Longitude Keluar',
                        'Jarak Keluar (m)',
                        'IP Address',
                        'User Agent'
                    ];
                }

                public function map($attendance): array
                {
                    return [
                        $attendance->user->name ?? '',
                        $attendance->present_date,
                        $attendance->present_at ? $attendance->present_at->format('H:i:s') : '',
                        $attendance->description,
                        $attendance->checkout_at ? $attendance->checkout_at->format('H:i:s') : '',
                        $attendance->work_duration_formatted ?: '-',
                        $attendance->latitude,
                        $attendance->longitude,
                        $attendance->distance,
                        $attendance->checkout_latitude,
                        $attendance->checkout_longitude,
                        $attendance->checkout_distance,
                        $attendance->ip_address,
                        $attendance->user_agent
                    ];
                }
            }, $filename);

        } catch (\Exception $e) {
            Log::error('Error exporting single attendance to Excel', ['error' => $e->getMessage(), 'id' => $id]);
            return back()->with('error', 'Error exporting data: ' . $e->getMessage());
        }
    }

}
