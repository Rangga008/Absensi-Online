<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Concession extends Model
{
    use SoftDeletes;

    protected $table = 'concession';
    
    protected $fillable = [
        'user_id',
        'reason',
        'description',
        'start_date',
        'end_date',
        'status',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime'
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'approved_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Relationship with user who requested
    // Relationship with user who requested
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with user who approved
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Accessor untuk format tanggal
    public function getFormattedStartDateAttribute()
    {
        return $this->start_date ? $this->start_date->format('d F Y') : 'N/A';
    }

    public function getFormattedEndDateAttribute()
    {
        return $this->end_date ? $this->end_date->format('d F Y') : 'N/A';
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d F Y H:i');
    }

    public function getFormattedApprovedAtAttribute()
    {
        return $this->approved_at ? $this->approved_at->format('d F Y H:i') : 'N/A';
    }

    // Scope for pending concessions
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope for approved concessions
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Scope for rejected concessions
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Get duration in days
    public function getDurationAttribute()
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInDays($this->end_date) + 1;
        }
        return 0;
    }

    // Check if concession is pending
    public function getIsPendingAttribute()
    {
        return $this->status === 'pending';
    }

    // Check if concession is approved
    public function getIsApprovedAttribute()
    {
        return $this->status === 'approved';
    }

    // Check if concession is rejected
    public function getIsRejectedAttribute()
    {
        return $this->status === 'rejected';
    }
}