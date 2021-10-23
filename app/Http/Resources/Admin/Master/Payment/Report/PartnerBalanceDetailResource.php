<?php

namespace App\Http\Resources\Admin\Master\Payment\Report;

use Illuminate\Http\Resources\Json\JsonResource;

class PartnerBalanceDetailResource extends JsonResource
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
