<?php

namespace App\Models\Deliveries;

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

    public function originWarehouse(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'origin_warehouse_id', 'id');
    }

    public function partnerDoorings(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_dooring_id', 'id');
    }
}
