<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoBlockedIdentifier extends Model
{
    protected $fillable = [
        'type',
        'identifier',
        'reason',
        'last_ip',
    ];
}
