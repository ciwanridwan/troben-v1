<?php

namespace App\Models;

use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use ReflectionClass;

class CodeLogable extends MorphPivot
{
    protected $table = 'code_logables';
    use HasFactory;
    const TYPE_ERROR = 'error';
    const TYPE_INFO = 'info';
    const TYPE_WARNING = 'warning';
    const TYPE_NEUTRAL = 'neutral';
    const TYPE_SCAN = 'scan';

    const SHOW_CUSTOMER = 'customer';
    const SHOW_PARTNER = 'partner';
    const SHOW_ADMIN = 'admin';
    const SHOW_ALL = self::SHOW_CUSTOMER . ',' . self::SHOW_PARTNER . ',' . self::SHOW_ADMIN;

    protected $hidden = [
        'id',
        'code_id',
        'code_logable_id',
        'code_logable_type',
        "type",
        "showable",
        "status",
        "description",
        "created_at",
        "updated_at",
        "deleted_at",
    ];

    public function code(): BelongsTo
    {
        return $this->belongsTo(Code::class, 'code_id', 'id');
    }

    public function code_logable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array
     */
    public static function getLogPackageStatusAvailables(): array
    {
        $statuses = [];
        foreach (Package::getStatusConst() as $status => $statusValue) {
            foreach (Package::getPaymentStatusConst() as $paymentStatus => $paymentStatusValue) {
                $statuses[$status . '_' . $paymentStatus] = $statusValue . '_' . $paymentStatusValue;
            }
        }
        return $statuses;
    }

    /**
     * @return array
     */
    public static function getLogDeliveryStatusAvailables(): array
    {
        $statuses = [];
        foreach (Delivery::getTypeConst() as $type => $typeValue) {
            foreach (Delivery::getStatusConst() as $status => $statusValue) {
                $statuses[$type . '_' . $status] = $typeValue . '_' . $statusValue;
            }
        }
        return $statuses;
    }

    public static function getAvailableStatusCode()
    {
        return array_flip(array_merge(self::getLogPackageStatusAvailables(), self::getLogDeliveryStatusAvailables()));
    }

    /**
     * Get error codes.
     *
     * @return string[]
     */
    public static function getStatusCode(): array
    {
        $class = new ReflectionClass(__CLASS__);

        return array_flip($class->getConstants());
    }

    public static function getAvailableTypes()
    {
        return [
            self::TYPE_ERROR,
            self::TYPE_INFO,
            self::TYPE_WARNING,
            self::TYPE_NEUTRAL,
            self::TYPE_SCAN
        ];
    }
}