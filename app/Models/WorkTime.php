<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WorkTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'late_threshold',
        'is_active',
        'description'
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'late_threshold' => 'datetime:H:i',
        'is_active' => 'boolean'
    ];

    /**
     * Get users assigned to this shift
     */
    public function users()
    {
        return $this->hasMany(User::class, 'shift_id');
    }

    /**
     * Get attendances for this shift
     */
    public function attendances()
    {
        return $this->hasManyThrough(Attendance::class, User::class, 'shift_id', 'user_id');
    }

    /**
     * Scope for active shifts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if a given time is late for this shift
     */
    public function isLate($checkTime = null)
    {
        try {
            if (empty($this->late_threshold)) {
                return false;
            }

            $checkTime = $checkTime ?: now()->format('H:i:s');
            $lateThreshold = Carbon::createFromFormat('H:i:s', $this->late_threshold);

            return Carbon::createFromFormat('H:i:s', $checkTime)->gt($lateThreshold);
        } catch (\Exception $e) {
            Log::warning('Error checking if time is late for WorkTime ID ' . $this->id . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get formatted start time
     */
    public function getFormattedStartTimeAttribute()
    {
        try {
            if (empty($this->start_time)) {
                return '--:--';
            }
            return Carbon::createFromFormat('H:i:s', $this->start_time)->format('H:i');
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::warning('Invalid start_time format for WorkTime ID ' . $this->id . ': ' . $this->start_time);
            return '--:--';
        }
    }

    /**
     * Get formatted end time
     */
    public function getFormattedEndTimeAttribute()
    {
        try {
            if (empty($this->end_time)) {
                return '--:--';
            }
            return Carbon::createFromFormat('H:i:s', $this->end_time)->format('H:i');
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::warning('Invalid end_time format for WorkTime ID ' . $this->id . ': ' . $this->end_time);
            return '--:--';
        }
    }

    /**
     * Get formatted late threshold
     */
    public function getFormattedLateThresholdAttribute()
    {
        try {
            if (empty($this->late_threshold)) {
                return '--:--';
            }
            return Carbon::createFromFormat('H:i:s', $this->late_threshold)->format('H:i');
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::warning('Invalid late_threshold format for WorkTime ID ' . $this->id . ': ' . $this->late_threshold);
            return '--:--';
        }
    }
}
