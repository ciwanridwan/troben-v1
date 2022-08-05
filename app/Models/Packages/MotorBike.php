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

    }
    
    public function packageItems()
    {

    }

    public function packagePrices()
    {

    }
}
