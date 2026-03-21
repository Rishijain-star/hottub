<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeaturedContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_type',
        'brand_id',
        'hot_tub_id',
        'title',
        'image_url',
        'featured_from',
        'featured_until',
        'show_on_homepage',
        'status',
        'slug',
    ];

    protected $casts = [
        'show_on_homepage' => 'boolean',
        'featured_from' => 'date',
        'featured_until' => 'date',
    ];

    public function hotTub()
    {
        return $this->belongsTo(HotTub::class, 'hot_tub_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
}

