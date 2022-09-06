<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'google_name',
        'google_placeid',
        'name',
        'regional_id',
    ];
}
