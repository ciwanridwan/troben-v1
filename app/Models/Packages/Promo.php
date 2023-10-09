<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    public const TYPE_DISCOUNT_SERVICE = 'discount-service';
    public const TYPE_DISCOUNT_PICKUP = 'discount-pickup';
    public const STATUS_PENDING = 'pending';
    public const STATUS_VALID = 'valid';
    public const STATUS_FAIL = 'fail';

    public const MIN_WEIGHT = 50;

    /**
     * Define a table
     */
    protected $table = 'package_promos';

    /**
     * Define a columns
     */
    protected $fillable = [
        'package_id',
        'type',
        'status',
        'meta'
    ];
}
