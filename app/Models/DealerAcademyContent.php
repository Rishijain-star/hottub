<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealerAcademyContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'content_type',
        'file_path',
        'thumbnail_path',
        'external_link',
        'category',
    ];
}
