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
        'phone',
        'postcode',
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
    ];

    public function activities()
    {
        return $this->hasMany(LeadActivity::class)->orderBy('created_at', 'desc');
    }

    public function purchases()
    {
        return $this->hasMany(LeadPurchase::class);
    }
}
