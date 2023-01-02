<?php

namespace App\Models\Deliveries;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRoute extends Model
{
    use HasFactory;

    protected $table = 'delivery_routes';

    protected $fillable = [
        'package_id',
        'regency_origin_id',
        'regency_destination_1',
        'regency_destination_2',
        'regency_destination_3',
        'reach_destination_1_at',
        'reach_destination_2_at',
        'reach_destination_3_at',
        'regency_dooring_id'
    ];
}
