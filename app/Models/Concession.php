<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Concession extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'concession';
    protected $dates = ['start_date', 'end_date', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}