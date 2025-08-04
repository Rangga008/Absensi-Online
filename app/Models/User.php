<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Role;
use App\Models\Salary;
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
}
