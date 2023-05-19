<?php

namespace App\Models;

use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerSatellite extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id_partner',

        'geo_province_id',
        'geo_regency_id',
        'geo_district_id',
        'geo_sub_district_id',

        'address',
        'display_name',
        'latitude',
        'longitude',
    ];

    public function parent()
    {
        return $this->belongsTo(Partner::class, 'id_partner');
    }
}
