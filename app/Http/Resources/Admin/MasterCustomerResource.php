<?php

namespace App\Http\Resources\Admin;

use Illuminate\Contracts\Support\Arrayable;
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
        $data['order'] = [
            'count' => $data['orderCount'],
            'payment' => $this->orders()->paid()->sum('total_payment'),
        ];
        unset($data['orderCount']);
        return $data;
    }
}
