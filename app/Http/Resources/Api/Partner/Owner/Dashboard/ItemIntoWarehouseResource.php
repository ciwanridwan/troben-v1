<?php

namespace App\Http\Resources\Api\Partner\Owner\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemIntoWarehouseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);  
    }
}