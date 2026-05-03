<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageRequest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'dealer_id', 'package_id', 'message', 'status', 'start_date', 'expiry_date', 'next_due_date', 'cancellation_type', 'cancellation_requested_at', 'cancellation_effective_at', 'cancelled_at', 'cancellation_reason'];

    protected $casts = [
        'start_date' => 'datetime',
        'expiry_date' => 'datetime',
        'next_due_date' => 'datetime',
        'cancellation_requested_at' => 'datetime',
        'cancellation_effective_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dealer()
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }

    public function package()
    {
        return $this->belongsTo(MaintenancePackage::class, 'package_id');
    }

    public function getOverdueAttribute()
    {
        return $this->status === 'pending' && $this->created_at->diffInHours(now()) >= 3;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }
}
