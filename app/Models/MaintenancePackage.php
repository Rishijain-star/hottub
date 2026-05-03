<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenancePackage extends Model
{
    use HasFactory;

    protected $fillable = ['dealer_id', 'name', 'price', 'plan_type', 'is_most_popular', 'description', 'features', 'status'];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'is_most_popular' => 'boolean',
    ];

    public function dealer()
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }

    public function requests()
    {
        return $this->hasMany(PackageRequest::class, 'package_id');
    }

    public function markAsMostPopular(): void
    {
        static::where('dealer_id', $this->dealer_id)
            ->where('id', '!=', $this->id)
            ->update(['is_most_popular' => false]);

        if (!$this->is_most_popular) {
            $this->is_most_popular = true;
            $this->save();
        }
    }
}
