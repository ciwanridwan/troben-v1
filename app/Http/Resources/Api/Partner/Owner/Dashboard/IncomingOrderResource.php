<?php

namespace App\Http\Resources\Api\Partner\Owner\Dashboard;

use App\Models\Service;
use Illuminate\Http\Resources\Json\JsonResource;

class IncomingOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        switch (true) {
            case $this->service_code === Service::TRAWLPACK_EXPRESS;
                $serviceType = Service::TPX;
                break;
            default:
                $serviceType = Service::TPS;
                break;
        }

        return [
            'created_at' => $this->created_at->format('d/m/y H:i:s'),
            'code' => $this->code->content,
            'service_type' => $serviceType,
            'category_name' => $this->items->map(function ($r) {
                $categoryName = $r->categories ? $r->categories->name : null;
                return $categoryName;
            })->first(),
            'total_qty' => $this->items->sum('qty'),
            'total_weight' => $this->total_weight,
            'sender_address' => $this->sender_address
        ];
    }
}
