<?php

namespace App\Http\Resources\Api\Pricings;

use Illuminate\Http\Resources\Json\JsonResource;

class CubicPriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            'amount' => $this->amount,
            'notes' => $this->notes,
            'service_code' => $this->service_code,
        ];
        
        return $data;
    }
}
