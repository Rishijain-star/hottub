<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageRequest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'dealer_id', 'package_id', 'message', 'status'];

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
}
