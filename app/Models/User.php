<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Role;
use App\Models\Salary;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'users';
    protected $guarded = [];
    

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function salary()
    {
        return $this->hasMany(Salary::class);
    }
    // app/Models/User.php

public function attendances()
{
    return $this->hasMany(Attendance::class);
}

// app/Models/User.php

public function getLastAttendanceAttribute()
{
    return $this->attendances()->orderBy('present_at', 'desc')->first();
}

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

}