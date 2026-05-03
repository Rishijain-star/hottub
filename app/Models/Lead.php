<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'session_id',
        'phone',
        'postcode',
        'address',
        'lead_postcode',
        'lead_lat',
        'lead_lng',
        'interests',
        'timeframe',
        'message',
        'price',
        'status',
        'is_national',
        'creator_id',
        'lead_source',
        'is_private',
        'stage',
        'deposit_confirmed',
        'deposit_requested_at',
        'assigned_dealer_id',
        'delivery_details',
        'invoice_path',
        'warranty_path',
    ];

    protected $casts = [
        'interests' => 'array',
        'price' => 'decimal:2',
        'delivery_details' => 'array',
        'is_national' => 'boolean',
        'is_private' => 'boolean',
        'deposit_confirmed' => 'boolean',
        'deposit_requested_at' => 'datetime', 
    ];

    public function activities()
    {
        return $this->hasMany(LeadActivity::class)->orderBy('created_at', 'desc');
    }

    public function purchases()
    {
        return $this->hasMany(LeadPurchase::class);
    }

    public function dealer()
    {
        return $this->belongsTo(User::class, 'assigned_dealer_id');
    }

    public function customerActivities()
    {
        // Find user by email first
        $user = User::where('email', $this->email)->first();
        if ($user) {
            return CustomerActivity::where('user_id', $user->id)->orderBy('created_at', 'desc');
        }
        
        // Fallback to session_id if provided
        if ($this->session_id) {
            return CustomerActivity::where('session_id', $this->session_id)->orderBy('created_at', 'desc');
        }
        
        // If no user or session, we only show activity for registered users (linked by email)
        return CustomerActivity::where('id', 0); // Empty query
    }
}
