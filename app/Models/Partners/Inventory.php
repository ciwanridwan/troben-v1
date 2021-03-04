<?php

namespace App\Models\Partners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventories';

    protected $fillable = [
        'name',
        'capacity',
        'height',
        'qty',
    ];
}
