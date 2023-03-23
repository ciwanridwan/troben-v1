<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Concerns\Controllers\CustomSerializeDate;
use App\Models\Payments\Payment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CancelOrder extends Model
{
    use CustomSerializeDate;

    /**
     * * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Payments\Payment[] $payments
     */

    /**Define type of cancel */
    public const TYPE_RETURN_TO_SENDER_ADDRESS = 'return_to_sender_address';
    public const TYPE_SENDER_TO_WAREHOUSE = 'sender_to_warehouse';

    protected $table = 'cancel_orders';

    protected $casts = [
        'pickup_price' => 'integer',
    ];

    protected $fillable = [
        'package_id',
        'type',
        'pickup_price'
    ];

    public static function getCancelTypes()
    {
        return [
            self::TYPE_RETURN_TO_SENDER_ADDRESS,
            self::TYPE_SENDER_TO_WAREHOUSE
        ];
    }

    /** Relations to paymenst table */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable', 'payable_type', 'payable_id', 'id');
    }

    /**Relation to packages tables */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }
}
