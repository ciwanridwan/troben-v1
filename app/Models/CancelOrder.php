<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancelOrder extends Model
{
    // use HasFactory;
    use CustomSerializeDate;

    protected $table = 'cancel_orders';

    protected $fillable = [
        'package_id',
        "type"
    ];
}
