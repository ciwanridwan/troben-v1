<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Handling model.
 *
 * @property int $id
 * @property string $name
 * @property float $price
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Handling extends Model
{
    use HasFactory;

    const TYPE_WEIGHT = 'weight';
    const TYPE_VOLUME = 'volume';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'handling';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'price',
        'type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
