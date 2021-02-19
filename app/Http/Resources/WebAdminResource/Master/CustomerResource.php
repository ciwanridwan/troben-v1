<?php

namespace App\Http\Resources\WebAdminResource\Master;

use App\Http\Resources\OrderResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'phone' => $this->phone,
            'name' => $this->name,
            'email' => $this->email,
            'orders' => [
                'count' => $this->orders->count(),
                'data' => OrderResource::collection($this->orders),
            ],
        ];
    }
}
