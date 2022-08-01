<?php

namespace App\Http\Resources\Api\Partner;

use App\Models\Partners\Voucher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherAEResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $agent = '';
        if ($this->creator) {
            $agent = $this->creator->name;
        }

        /** @var Voucher $this */
        return [
            'title' => $this->title,
            'image' => 'https://blog.trawlbens.id/wp-content/uploads/2022/04/banner-voucher-1.jpg',
            'expired' => $this->expired->format('Y-m-d H:i:s'),
            'code' => $this->code,
            'type' => $this->type,
            'discount' => $this->discount,
            'nominal' => $this->nominal,
            'is_approved' => $this->is_approved,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'agent' => $agent,
        ];
    }
}
