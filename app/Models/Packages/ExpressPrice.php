<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpressPrice extends Model
{
    use HasFactory;

    protected $table = 'express_prices';

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


    protected $casts = [
        'amount' => 'float',
        'zip_code' => 'int'
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at'
    ];
}
