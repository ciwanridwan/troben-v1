<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapMappingPending extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'google_name',
        'lat',
        'lon',
    ];
}
