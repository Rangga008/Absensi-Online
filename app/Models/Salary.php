<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Salary extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'salary';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
