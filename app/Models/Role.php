<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app/Models/Role.php
class Role extends Model
{
    use HasFactory;
    
    protected $table = 'roles';
    protected $guarded = [];
    
    // Tambahkan ini untuk definisi kolom
    protected $fillable = [
        'role_name',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    // Jika ingin mengakses sebagai 'name' bukan 'role_name'
    public function getNameAttribute()
    {
        return $this->role_name;
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}