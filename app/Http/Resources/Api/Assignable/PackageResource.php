<?php

namespace App\Http\Resources\Api\Assignable;

use App\Http\Resources\Api\Package\ItemResource;
use App\Http\Resources\Geo\DistrictResource;
use App\Http\Resources\Geo\RegencyResource;
use App\Http\Resources\Geo\SubDistrictResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if (! $this->resource->relationLoaded('code')) {
            $this->resource->load('code');
        }
        
        if ($this->resource->relationLoaded('items')) {
            $items = ItemResource::collection($this->resource->items)->toArray($request);
            $this->resource->unsetRelation('items');
        }

        if (isset($items)) {
            $item = collect($items)->map(function ($q) {
                return [
                    'qty' => $q['qty'],
                    'name' => $q['name']
                ];
            })->toArray();
        }
        
        return [
            'id' => $this->id,
            'hash' => $this->hash,
            'total_weight' => $this->total_weight,
            'receiver_address' => $this->receiver_address,
            'code' => $this->code->only('content'),
            'items' => $item,
            'destination_regency' => $this->resource->destination_regency ? RegencyResource::make($this->resource->destination_regency)->only('name') : null,
            'destination_district' => $this->resource->destination_district ? DistrictResource::make($this->resource->destination_district)->only('name') : null,
            'destination_sub_district' => SubDistrictResource::make($this->resource->destination_sub_district)->only('name'),
        ];
    }
}
