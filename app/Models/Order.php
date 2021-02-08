<?php

namespace App\Models;

use App\Models\Packages\Package;
use App\Models\Customers\Customer;
use App\Concerns\Models\HasPhoneNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Order model.
 *
 * @property int $id
 * @property int $customer_id
 * @property string $barcode
 * @property string $sender_name
 * @property string $sender_phone
 * @property float $est_payment
 * @property float $total_payment
 * @property string $payment_channel
 * @property string $payment_ref_id
 * @property string $payment_status
 * @property int $est_weight
 * @property int $est_height
 * @property int $est_length
 * @property int $est_width
 * @property string $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property-read \App\Models\Customers\Customer|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Packages\Package[] $packages
 */
class Order extends Model
{
    use HasPhoneNumber, SoftDeletes;

    /**
     * Phone number column.
     *
     * @var string
     */
    protected $phoneNumberColumn = 'sender_phone';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $fillable = [
        'customer_id',
        'barcode',
        'sender_name',
        'sender_phone',
        'est_payment',
        'total_payment',
        'payment_channel',
        'payment_ref_id',
        'payment_status',
        'est_weight',
        'est_height',
        'est_length',
        'est_width',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'est_payment' => 'float',
        'total_payment' => 'float',
        'est_weight' => 'int',
        'est_height' => 'int',
        'est_length' => 'int',
        'est_width' => 'int',
    ];

    /**
     * Define `belongsTo` relationship with Customer model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * Define `hasMany` relationship with Package model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function packages(): HasMany
    {
        return $this->hasMany(Package::class, 'order_id', 'id');
    }
}
