<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadPurchase extends Model
{
    use HasFactory;

    protected $fillable = ['lead_id', 'dealer_id', 'buyer_role', 'amount'];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function dealer()
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }
}
