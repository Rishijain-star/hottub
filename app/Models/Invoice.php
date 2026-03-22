<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'dealer_id',
        'credits',
        'amount',
        'status',
        'payment_id',
    ];

    protected $casts = [
        'credits' => 'integer',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }
}

