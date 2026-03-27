<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'dealer_id', 'type', 'product_id', 'product_name', 'message', 
        'checklist_data', 'customer_review', 'customer_signature', 'status', 'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'checklist_data' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dealer()
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }

    public function product()
    {
        // Polymorphic or dynamic based on type
        if ($this->type === 'part') {
            return $this->belongsTo(Part::class, 'product_id');
        } elseif ($this->type === 'service') {
            return $this->belongsTo(Service::class, 'product_id');
        }
        return null;
    }

    public function getOverdueAttribute()
    {
        return $this->status === 'pending' && $this->created_at->diffInHours(now()) >= 3;
    }
}
