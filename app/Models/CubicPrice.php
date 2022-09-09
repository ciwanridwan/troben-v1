<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CubicPrice extends Model
{
    use HasFactory;

    protected $table = 'cubic_prices';

    protected $fillable = [
        'origin_province_id',
        'origin_regency_id',
        'origin_district_id',
        'origin_sub_district_id',
        'destination_id',
        'zip_code',
        'amount',
        'notes',
        'service_code'
    ];    
}
