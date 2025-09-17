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
        'description',
        'latitude',
        'longitude',
        'photo_path',
        'ip_address',
        'user_agent',
        'distance',
        'present_date',
        'present_at',
        'checkout_at',
        'checkout_latitude',
        'checkout_longitude',
        'checkout_photo_path',
        'checkout_distance',
       'checkout_user_agent',        'checkout_ip_address',
       'work_duration_minutes',
    ];

    protected $casts = [
        'present_at' => 'datetime',
        'checkout_at' => 'datetime',
        'present_date' => 'date',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'checkout_latitude' => 'decimal:7',
        'checkout_longitude' => 'decimal:7',
        'distance' => 'decimal:2',
        'checkout_distance' => 'decimal:2',
       'work_duration_minutes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $dates = [
        'present_at',
        'present_date',
        'checkout_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for today's attendance
     */
    public function scopeToday($query)
    {
        return $query->whereDate('present_date', now('Asia/Jakarta')->format('Y-m-d'));
    }

    public function getCheckoutPhotoUrlAttribute()
    {
        return $this->checkout_photo_path ? asset('storage/' . $this->checkout_photo_path) : null;
    }

    public function getFormattedCheckoutTimeAttribute()
    {
        return $this->checkout_at ? $this->checkout_at->setTimezone('Asia/Jakarta')->format('H:i:s') : null;
    }

    public function hasCheckedOut()
    {
        return !is_null($this->checkout_at);
    }

    public function getWorkDurationFormattedAttribute()
    {
        $minutes = $this->work_duration_minutes;

        // If not set, calculate from present_at to checkout_at
        if (!$minutes && $this->checkout_at && $this->present_at) {
            $minutes = $this->checkout_at->diffInMinutes($this->present_at);
        }

        if (!$minutes) {
            return '0 jam 0 menit';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return sprintf('%d jam %d menit', $hours, $remainingMinutes);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('present_date', [$startDate, $endDate]);
    }

    /**
     * Scope for specific month
     */
    public function scopeMonth($query, $month, $year = null)
    {
        $year = $year ?? Carbon::now()->year;
        return $query->whereMonth('present_date', $month)
                    ->whereYear('present_date', $year);
    }

    /**
     * Get photo URL attribute
     */
    public function getPhotoUrlAttribute()
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }

    /**
     * Accessor for formatted time
     */
    public function getFormattedTimeAttribute()
    {
        return $this->present_at ? $this->present_at->setTimezone('Asia/Jakarta')->format('H:i:s') : null;
    }

    /**
     * Accessor for formatted date
     */
    public function getFormattedDateAttribute()
    {
        return $this->present_date ? $this->present_date->format('d F Y') : null;
    }

    /**
     * Accessor for formatted datetime
     */
    public function getFormattedDateTimeAttribute()
    {
        return $this->present_at ? $this->present_at->setTimezone('Asia/Jakarta')->format('d F Y, H:i:s') : null;
    }

    /**
     * Accessor for status badge class
     */
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

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute()
    {
        switch ($this->description) {
            case 'Hadir':
                return 'success';
            case 'Terlambat':
                return 'warning';
            case 'Sakit':
                return 'info';
            case 'Izin':
                return 'secondary';
            case 'Dinas Luar':
                return 'primary';
            case 'WFH':
                return 'dark';
            default:
                return 'secondary';
        }
    }

    /**
     * Check if location is available
     */
    public function hasLocation()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    /**
     * Get distance from office (if location is available)
     */
    public function getDistanceFromOffice($officeLat = -6.906000000000, $officeLng = 107.623400000000)
    {
        if (!$this->hasLocation()) {
            return $this->distance; // Return stored distance if available
        }

        return $this->calculateDistance(
            $this->latitude,
            $this->longitude,
            $officeLat,
            $officeLng
        );
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    public static function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
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
     * Check if attendance is late (after 08:00)
     */
    public function isLate()
    {
        if (!$this->present_at) return false;
        
        $attendanceTime = $this->present_at->setTimezone('Asia/Jakarta')->format('H:i:s');
        return $attendanceTime > '08:00:00';
    }

    /**
     * Check if attendance is on time
     */
    public function getIsOnTimeAttribute()
    {
        if (!$this->present_at) return false;
        
        $attendanceTime = Carbon::parse($this->present_at)->setTimezone('Asia/Jakarta');
        $lateThreshold = $attendanceTime->copy()->setTime(8, 0, 0);
        
        return $attendanceTime->lte($lateThreshold);
    }

    /**
     * Check if attendance is on weekend
     */
    public function isWeekend()
    {
        if (!$this->present_date) return false;
        return Carbon::parse($this->present_date)->isWeekend();
    }

    /**
     * Get attendance statistics for a user in a specific month
     */
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

    /**
     * Check if user has attended today
     */
    public static function hasAttendedToday($userId)
    {
        $today = now('Asia/Jakarta')->format('Y-m-d');
        return self::forUser($userId)->whereDate('present_date', $today)->exists();
    }

    /**
     * Get today's attendance for a user
     */
    public static function getTodayAttendance($userId)
    {
        $today = now('Asia/Jakarta')->format('Y-m-d');
        return self::forUser($userId)->whereDate('present_date', $today)->first();
    }

    /**
     * Get latest attendance for user
     */
    public function latestAttendance()
    {
        return $this->hasOne(Attendance::class)->latestOfMany('present_at');
    }
}