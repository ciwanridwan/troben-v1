<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Concerns\Controllers\CustomSerializeDate;

class CancelOrder extends Model
{
    // use HasFactory;
    use CustomSerializeDate;

    /**Define type of cancel */
    public const TYPE_RETURN_TO_SENDER_ADDRESS = 'return_to_sender_address';
    public const TYPE_SENDER_TO_WAREHOUSE = 'sender_to_warehouse';

    protected $table = 'cancel_orders';

    protected $fillable = [
        'package_id',
        "type"
    ];

    public static function getCancelTypes()
    {
        return [
            self::TYPE_RETURN_TO_SENDER_ADDRESS,
            self::TYPE_SENDER_TO_WAREHOUSE
        ];
    }
}
