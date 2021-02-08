<?php

namespace App\Models\Deliveries;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    const TYPE_PICKUP = 'pickup';
    const TYPE_RETURN = 'return';
    const TYPE_TRANSIT = 'transit';
    const TYPE_DOORING = 'dooring';

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EN_ROUTE = 'en-route';
    const STATUS_FINISHED = 'finished';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'deliveries';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'transporter_id',
    ];

    /**
     * Get all available types.
     *
     * @return string[]
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_PICKUP,
            self::TYPE_RETURN,
            self::TYPE_TRANSIT,
            self::TYPE_DOORING,
        ];
    }

    /**
     * Get all available statuses.
     *
     * @return string[]
     */
    public static function getAvailableStatus(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_ACCEPTED,
            self::STATUS_CANCELLED,
            self::STATUS_EN_ROUTE,
            self::STATUS_FINISHED,
        ];
    }
}
