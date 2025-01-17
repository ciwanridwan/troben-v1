<?php

namespace App\Models\Deliveries;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Models\Code;
use App\Models\Partners\Performances\PerformanceModel;
use App\Models\User;
use App\Models\Packages\Item;
use App\Concerns\Models\HasCode;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\Payments\Payment;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use ReflectionClass;

/**
 * Class Delivery.
 *
 * @property int id
 * @property int origin_partner_id
 * @property int partner_id
 * @property string type
 * @property string status
 * @property int origin_regency_id
 * @property int origin_district_id
 * @property int origin_sub_district_id
 * @property int destination_regency_id
 * @property int destination_district_id
 * @property int destination_sub_district_id
 * @property string sender_latitude
 * @property string sender_longitude
 * @property string receiver_latitude
 * @property string receiver_longitude
 * @property int created_by
 * @property int updated_by
 * @property-read Partner partner
 * @property-read \Illuminate\Database\Eloquent\Collection packages
 * @property \Illuminate\Database\Eloquent\Collection item_codes
 * @property-read string|null as
 * @property int|null userable_id
 * @property-read Partner $origin_partner
 * @property-read Transporter $transporter
 * @property-read Code $code
 * @property-read \App\Models\Partners\Performances\Delivery|null $partner_performance
 */
class Delivery extends Model
{
    use HashableId, HasCode, HasFactory, CustomSerializeDate;

    public const DELIVERY_SYSTEM_ID = 0;

    public const TYPE_PICKUP = 'pickup';
    public const TYPE_RETURN = 'return';
    public const TYPE_TRANSIT = 'transit';
    public const TYPE_DOORING = 'dooring';

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_WAITING_ASSIGN_PACKAGE = 'waiting_assign_package';
    public const STATUS_WAITING_ASSIGN_PARTNER = 'waiting_assign_partner';
    public const STATUS_WAITING_PARTNER_ASSIGN_TRANSPORTER = 'waiting_partner_assign_transporter';
    public const STATUS_WAITING_ASSIGN_TRANSPORTER = 'waiting_assign_transporter';
    public const STATUS_WAITING_TRANSPORTER = 'waiting_transporter';
    public const STATUS_LOADING = 'loading';
    public const STATUS_EN_ROUTE = 'en-route';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_REJECTED = 'rejected';

    public const AS_ORIGIN = 'origin';
    public const AS_DESTINATION = 'destination';

    public const FEE_MAIN = 500;
    public const FEE_PERCENTAGE_BUSINESS = 0.3;
    public const FEE_PERCENTAGE_SPACE = 0.3; // default 0.2
    public const FEE_PERCENTAGE_POS = 0.15;
    public const FEE_PERCENTAGE_HEADSALES = 0.5;
    public const FEE_PERCENTAGE_SALES = 0.2;
    public const FEE_JABODETABEK = 200;
    public const FEE_NON_JABODETABEK = 250;
    public const FEE_FREE_PICKUP = 1; // of percentage is 100%

    protected string $codeType = 'MNF';

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
        'origin_partner_id',
        'partner_id',
        'userable_id',
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
        'origin_partner_id',
        'partner_id',
        'userable_id',
        'origin_regency_id',
        'origin_district_id',
        'origin_sub_district_id',
        'destination_regency_id',
        'destination_district_id',
        'destination_sub_district_id',
    ];


    /**
     * Get error codes.
     *
     * @return string[]
     */
    public static function getTypeConst(): array
    {
        $class = new ReflectionClass(__CLASS__);
        return array_filter($class->getConstants(), fn ($key) => str_starts_with($key, 'TYPE'), ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get error codes.
     *
     * @return string[]
     */
    public static function getStatusConst(): array
    {
        $class = new ReflectionClass(__CLASS__);
        return array_filter($class->getConstants(), fn ($key) => str_starts_with($key, 'STATUS'), ARRAY_FILTER_USE_KEY);
    }

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
            self::STATUS_WAITING_ASSIGN_PACKAGE,
            self::STATUS_WAITING_ASSIGN_PARTNER,
            self::STATUS_WAITING_PARTNER_ASSIGN_TRANSPORTER,
            self::STATUS_WAITING_ASSIGN_TRANSPORTER,
            self::STATUS_WAITING_TRANSPORTER,
            self::STATUS_LOADING,
            self::STATUS_EN_ROUTE,
            self::STATUS_FINISHED,
        ];
    }

    public function packages(): Relations\MorphToMany
    {
        return $this->morphedByMany(Package::class, 'deliverable')
            ->withPivot(['status', 'created_at', 'updated_at'])
            ->withTimestamps()
            ->orderByPivot('created_at')
            ->using(Deliverable::class);
    }

    /**
     * Define `morphMany` relationship with Payment model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable', 'payable_type', 'payable_id', 'id');
    }

    public function item_codes(): Relations\MorphToMany
    {
        return $this->morphedByMany(Code::class, 'deliverable')
            ->withPivot(['is_onboard', 'status', 'created_at', 'updated_at'])
            ->withTimestamps()
            ->orderByPivot('created_at')
            ->using(Deliverable::class)
            ->whereHasMorph('codeable', Item::class);
    }

    /**
     * @return MorphOne
     */
    public function code(): MorphOne
    {
        return $this->morphOne(Code::class, 'codeable');
    }

    /**
     * define relation who is create this delivery it is used for delivery.type transit
     * when app need to know origin and destination of the partner, delivery can seen
     * on those two partner this column is for origin_partner.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function origin_partner(): Relations\BelongsTo
    {
        return $this->belongsTo(Partner::class, 'origin_partner_id');
    }

    public function partner(): Relations\BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function assigned_to(): Relations\BelongsTo
    {
        return $this->belongsTo(UserablePivot::class, 'userable_id', 'id');
    }

    public function driver(): Relations\HasOneThrough
    {
        return $this->hasOneThrough(User::class, UserablePivot::class, 'id', 'id', 'userable_id', 'user_id');
    }

    public function transporter(): Relations\HasOneThrough
    {
        return $this->hasOneThrough(Transporter::class, UserablePivot::class, 'id', 'id', 'userable_id', 'userable_id')
            ->where('userables.userable_type', Transporter::class);
    }

    public function getAsAttribute(): ?string
    {
        /** @var PartnerRepository $repository */
        $repository = app(PartnerRepository::class);
        $as = null;

        if ($repository->getScopedRole() === UserablePivot::ROLE_DRIVER) {
            return 'driver';
        }

        switch ($repository->getPartner()->id) {
            case $this->partner_id:
                $as = self::AS_DESTINATION;
                break;
            case $this->origin_partner_id:
                $as = self::AS_ORIGIN;
                break;
        }

        return $as;
    }


    public static function getAvailableDescriptionFormat()
    {
        return [
            [
                'type' => [self::TYPE_PICKUP],
                'status' => [self::STATUS_ACCEPTED],
                'description' => 'Pesanan siap dijemput oleh :driver_name [:transporter_registration_number]',
                'variable' => ['driver_name', 'transporter_registration_number']
            ],
            [
                'type' => [self::TYPE_PICKUP],
                'status' => [self::STATUS_WAITING_TRANSPORTER],
                'description' => ':driver_name sedang dalam perjalanan ke tempat kamu',
                'variable' => ['driver_name']
            ],
            [
                'type' => [self::TYPE_PICKUP, self::TYPE_TRANSIT],
                'status' => [self::STATUS_FINISHED],
                'description' => 'Pesanan kamu telah ada di Mitra :partner_name [:partner_code]',
                'variable' => ['partner_name', 'partner_code']
            ],
            [
                'type' => [self::TYPE_TRANSIT],
                'status' => [self::STATUS_WAITING_TRANSPORTER],
                'description' => ':driver_name sedang dalam perjalanan ke :origin_partner_name [:origin_partner_code]',
                'variable' => ['driver_name', 'origin_partner_name', 'origin_partner_code']
            ],
            [
                'type' => [self::TYPE_TRANSIT],
                'status' => [self::STATUS_EN_ROUTE],
                'description' => ':driver_name sedang dalam perjalanan ke :partner [:partner_code]',
                'variable' => ['driver_name', 'partner', 'partner_code']
            ],
            [
                'type' => [self::TYPE_PICKUP],
                'status' => [self::STATUS_EN_ROUTE],
                'description' => ':driver_name telah mengambil barang dari customer dan sedang dalam perjalanan ke :partner [:partner_code]',
                'variable' => ['driver_name', 'partner', 'partner_code']
            ],
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function partner_performance(): HasOne
    {
        return $this->hasOne(\App\Models\Partners\Performances\Delivery::class, 'delivery_id', 'id')
            ->where('status', PerformanceModel::STATUS_ON_PROCESS)
            ->orderBy('created_at', 'desc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sla(): HasOne
    {
        return $this->hasOne(\App\Models\Partners\Performances\Delivery::class, 'delivery_id', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * List of departure order
     */
    public static function getStatusDeparture(): array
    {
        return [
            self::STATUS_ACCEPTED,
            self::STATUS_EN_ROUTE
        ];
    }
}
