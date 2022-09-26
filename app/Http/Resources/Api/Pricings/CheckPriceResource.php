<?php

namespace App\Http\Resources\Api\Pricings;

use App\Models\Service;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckPriceResource extends JsonResource
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

        switch ($data['service_code']) {
            case Service::TRAWLPACK_STANDARD:
                $data['amount'] = $this->tier_1;
                $data['delivery_method'] = 'Regular';
                return $data;
                break;

            case Service::TRAWLPACK_CUBIC:
                $data['delivery_method'] = 'Kubikasi';
                return $data;
                break;
            case Service::TRAWLPACK_EXPRESS:
                $data['delivery_method'] = 'Express';
                return $data;
                break;
        }
    }
}
