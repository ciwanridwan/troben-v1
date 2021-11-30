<?php

namespace App\Http\Resources\Api\Package;

use Illuminate\Http\Resources\Json\JsonResource;

class PackagePromotionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        dd($this->sender_name);
        return parent::toArray($request);
    }
}
