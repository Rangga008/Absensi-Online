<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'attendance';

    protected $fillable = [
        'user_id',
        'present_at',
        'description',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'present_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $dates = [
        'present_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope for today's attendance
    public function scopeToday($query)
    {
        return $query->whereDate('present_at', Carbon::today());
    }

    // Scope for specific user
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope for date range
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('present_at', [$startDate, $endDate]);
    }

    // Scope for specific month
    public function scopeMonth($query, $month, $year = null)
    {
        $year = $year ?? Carbon::now()->year;
        return $query->whereMonth('present_at', $month)
                    ->whereYear('present_at', $year);
    }

    // Accessor for formatted time
    public function getFormattedTimeAttribute()
    {
        return $this->present_at->format('H:i:s');
    }

    // Accessor for formatted date
    public function getFormattedDateAttribute()
    {
        return $this->present_at->format('d F Y');
    }

    // Accessor for formatted datetime
    public function getFormattedDateTimeAttribute()
    {
        return $this->present_at->format('d F Y, H:i:s');
    }

    // Accessor for status badge class
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->description) {
            case 'Hadir':
                return 'badge-success';
            case 'Terlambat':
                return 'badge-warning';
            case 'Sakit':
                return 'badge-info';
            case 'Izin':
                return 'badge-secondary';
            case 'Dinas Luar':
                return 'badge-primary';
            case 'WFH':
                return 'badge-dark';
            default:
                return 'badge-light';
        }
    }

    // Check if location is available
    public function hasLocation()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    // Get distance from office (if location is available)
    public function getDistanceFromOffice($officeLat = -6.906000000000, $officeLng = 107.623400000000)
    {
        if (!$this->hasLocation()) {
            return null;
        }

        return $this->calculateDistance(
            $this->latitude,
            $this->longitude,
            $officeLat,
            $officeLng
        );
    }

    // Calculate distance between two coordinates using Haversine formula
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

    // Check if attendance is late (after 08:00)
    public function isLate()
    {
        $attendanceTime = $this->present_at->format('H:i:s');
        return $attendanceTime > '08:00:00';
    }

    // Check if attendance is on weekend
    public function isWeekend()
    {
        return $this->present_at->isWeekend();
    }

    // Get attendance statistics for a user in a specific month
    public static function getMonthlyStats($userId, $month = null, $year = null)
    {
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;

        $attendances = self::forUser($userId)->month($month, $year)->get();

        return [
            'total' => $attendances->count(),
            'hadir' => $attendances->where('description', 'Hadir')->count(),
            'terlambat' => $attendances->where('description', 'Terlambat')->count(),
            'sakit' => $attendances->where('description', 'Sakit')->count(),
            'izin' => $attendances->where('description', 'Izin')->count(),
            'dinas_luar' => $attendances->where('description', 'Dinas Luar')->count(),
            'wfh' => $attendances->where('description', 'WFH')->count(),
        ];
    }

    // Check if user has attended today
    public static function hasAttendedToday($userId)
    {
        return self::forUser($userId)->today()->exists();
    }

    // Get today's attendance for a user
    public static function getTodayAttendance($userId)
    {
        return self::forUser($userId)->today()->first();
    }
}