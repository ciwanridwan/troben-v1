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
            'total_amount' => (float) $this->resource['total_amount'],
            'detail' => HistoryResource::collection($this->resource['data']->getCollection()),
        ];
    }
}
