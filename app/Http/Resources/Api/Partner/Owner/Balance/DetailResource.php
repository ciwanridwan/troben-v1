<?php

namespace App\Http\Resources\Api\Partner\Owner\Balance;

use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'total_amount' => $this->resource->getCollection()->sum(fn ($collect) => $collect->balance),
            'detail' => HistoryResource::collection($this->resource->getCollection()),
        ];
    }
}
