<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageCorporate extends Model
{
    public const CORPORATE_PAYMENT_VA = 'va';
    public const CORPORATE_PAYMENT_CASH = 'cash';
    public const CORPORATE_PAYMENT_TOP = 'top';
    public const CORPORATE_PAYMENT_ALL = [
        self::CORPORATE_PAYMENT_VA,
        self::CORPORATE_PAYMENT_CASH,
        self::CORPORATE_PAYMENT_TOP,
    ];

    protected $fillable = [
        'package_id',
        'payment_method',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
