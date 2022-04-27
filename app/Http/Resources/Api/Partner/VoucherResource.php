<?php

namespace App\Http\Resources\Api\Partner;

use App\Http\Resources\Geo\RegencyResource;
use App\Models\Partners\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Voucher $this */
        return [
            'title' => $this->title,
            'partner' => $this->partner->code,
//            'image' => $this->attachments()->first()->uri ?? null,
            'image' => 'https://blog.trawlbens.id/wp-content/uploads/2022/04/banner-voucher-1.jpg',
            'expired' => $this->end_date->format('Y-m-d H:i:s'),
            'code' => $this->code,
            'discount' => $this->discount,
            'is_approved' => $this->is_approved,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
