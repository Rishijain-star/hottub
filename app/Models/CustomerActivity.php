<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'page_name',
        'url',
        'product_id',
        'product_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
