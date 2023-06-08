<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapMappingIgnore extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'google_name',
        'google_placeid',
        'lat',
        'lon',
    ];
}
