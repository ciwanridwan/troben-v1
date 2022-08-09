<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotorBike extends Model
{
    use HasFactory;

    protected $table = 'package_bikes';

    protected $fillable = 
    [
        'type',
        'merk',
        'cc',
        'years',
    ];


    public function packages()
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }
    
    public function packageItems()
    {
        return $this->belongsTo(Package::class, 'package_item_id', 'id');
    }

    public function packagePrices()
    {

    }
}
