<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BikePrices extends Model
{
    use HasFactory;

    protected $table = 'bike_prices';

    protected $fillable = [
        'origin_province_id',
        'origin_regency_id',
        'origin_district_id',
        'origin_sub_district_id',
        'zip_code',
        'destination_id',
        'lower_cc',
        'middle_cc',
        'high_cc',
        'notes',
        'service_code'
    ];
}
