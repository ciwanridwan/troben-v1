<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class MasterCustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = $this->resource->toArray();
        $data['package'] = [
            'count' => $data['packageCount'],
            'payment' => $data['packageTotalPayment'],
        ];
        unset($data['packageCount'], $data['packageTotalPayment']);

        return $data;
    }
}
