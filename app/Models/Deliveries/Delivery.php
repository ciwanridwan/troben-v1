<?php

namespace App\Models\Deliveries;

use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Concerns\Models\HasBarcode;
use App\Models\Partners\Transporter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * Class Delivery
 * @package App\Models\Deliveries
 *
 * @property int id
 * @property int partner_id
 * @property int transporter_id
 * @property string barcode
 * @property string type
 * @property string status
 * @property int origin_regency_id
 * @property int origin_district_id
 * @property int origin_sub_district_id
 * @property int destination_regency_id
 * @property int destination_district_id
 * @property int destination_sub_district_id
 */
class Delivery extends Model
{
    use HashableId, HasBarcode;

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
        'partner_id',
        'transporter_id',
        'barcode',
        'type',
        'status',
        'origin_regency_id',
        'origin_district_id',
        'origin_sub_district_id',
        'destination_regency_id',
        'destination_district_id',
        'destination_sub_district_id',
    ];

    protected $appends = [
        'hash',
    ];

    protected $hidden = [
        'id',
        'partner_id',
        'transporter_id',
        'origin_regency_id',
        'origin_district_id',
        'origin_sub_district_id',
        'destination_regency_id',
        'destination_district_id',
        'destination_sub_district_id',
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

    public function packages(): Relations\BelongsToMany
    {
        return $this->belongsToMany(Package::class)
            ->withPivot(['is_onboard', 'created_at', 'updated_at'])
            ->withTimestamps()
            ->orderByPivot('created_at')
            ->using(DeliveryPackagePivot::class);
    }

    public function partner(): Relations\BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function transporter(): Relations\BelongsTo
    {
        return $this->belongsTo(Transporter::class);
    }
}
