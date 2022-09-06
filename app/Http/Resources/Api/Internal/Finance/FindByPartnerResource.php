<?php

namespace App\Http\Resources\Api\Internal\Finance;

use Illuminate\Http\Resources\Json\JsonResource;

class FindByPartnerResource extends JsonResource
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
            'id' => $this['id'],
            'name' => $this['name'],
            'code' => $this['code'],
            'balance' => $this['balance'],
        ];

        return $data;
    }
}
