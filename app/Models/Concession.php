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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
