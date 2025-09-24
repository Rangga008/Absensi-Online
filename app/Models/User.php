<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Role;
use App\Models\Salary;
use App\Models\Attendance;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use HasFactory, Notifiable, SoftDeletes;
    protected $table = 'users';
    protected $guarded = [];
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'role_id',
        'shift_id',
    ];

    /**
     * Get the shift assigned to the user
     */
    public function shift()
    {
        return $this->belongsTo(WorkTime::class, 'shift_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function salary()
    {
        return $this->hasMany(Salary::class);
    }

    /**
     * Get the last attendance record
     */
    public function getLastAttendanceAttribute()
    {
        return $this->attendances()->orderBy('present_at', 'desc')->first();
    }

    /**
     * Get the latest attendance record
     */
    public function latestAttendance()
    {
        return $this->hasOne(Attendance::class)->latestOfMany('present_at');
    }

    /**
     * Get status badge color for attendance
     */
    public function getStatusBadgeAttribute()
    {
        $status = $this->description ?? '';

        switch($status) {
            case 'Hadir': return 'success';
            case 'Terlambat': return 'warning';
            case 'Sakit': return 'info';
            case 'Izin': return 'secondary';
            case 'Dinas Luar': return 'primary';
            case 'WFH': return 'dark';
            default: return 'light';
        }
    }



    /**
     * Get all attendances for the user.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get all concessions for the user.
     */
    public function concessions()
    {
        return $this->hasMany(Concession::class);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin()
    {
        return $this->role_id === 1;
    }

    /**
     * Check if user is soft deleted.
     */
    public function isDeleted()
    {
        return $this->trashed();
    }

    /**
     * Get user's full address.
     */
    public function getFullAddressAttribute()
    {
        return $this->address;
    }

}