<?php

namespace App\Http\Resources\Api\Partner\Owner;

use Illuminate\Http\Resources\Json\JsonResource;

class InfoProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $partner = $this->resource->partners->first();
        $partnerResult = $partner ? $partner->only('code', 'address') : null;

        $bankOwner = $this->resource->bankOwner ? $this->resource->bankOwner->only('account_name', 'account_number') : null;
        $bank = $this->resource->bankOwner ? $this->resource->bankOwner->banks->only('name') : null;

        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'partner' => $partnerResult,
            'bank_account' => array_merge($bank, $bankOwner),
            'avatar' => $this->avatar
        ];
    }
}
