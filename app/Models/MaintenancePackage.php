<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenancePackage extends Model
{
    use HasFactory;

    protected $fillable = ['dealer_id', 'name', 'price', 'description', 'features', 'status'];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
    ];

    public function dealer()
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }

    public function requests()
    {
        return $this->hasMany(PackageRequest::class, 'package_id');
    }
}
