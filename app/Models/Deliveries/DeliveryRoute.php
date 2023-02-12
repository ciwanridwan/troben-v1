<?php

namespace App\Models\Deliveries;

use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryRoute extends Model
{
    use HasFactory;

    protected $table = 'delivery_routes';

    protected $fillable = [
        'package_id',
        'origin_warehouse_id',
        'regency_origin_id',
        'regency_destination_1',
        'regency_destination_2',
        'regency_destination_3',
        'reach_destination_1_at',
        'reach_destination_2_at',
        'reach_destination_3_at',
        'regency_dooring_id',
        'partner_dooring_id'
    ];

    protected $appends = [
        'transit_count'
    ];

    public function originWarehouse(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'origin_warehouse_id', 'id');
    }

    public function partnerDoorings(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_dooring_id', 'id');
    }

    public function packages(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }


    public function getTransitCountAttribute(): int
    {
        switch (true) {
            case ! is_null($this->reach_destination_1_at) && ! is_null($this->reach_destination_2_at) && ! is_null($this->reach_destination_3_at):
                $transit = 3;
                break;
            case ! is_null($this->reach_destination_1_at) && ! is_null($this->reach_destination_2_at):
                $transit = 2;
                break;
            case ! is_null($this->reach_destination_1_at):
                $transit = 1;
                break;
            default:
                $transit = 0;
                break;
        }

        return $transit;
    }
}
