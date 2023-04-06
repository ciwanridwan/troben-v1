<?php

namespace App\Http\Resources\Api\Partner\Owner;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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

        if (!is_null($bankOwner)) {
            $bank = $this->resource->bankOwner->banks->only('id', 'name') ?? null;
            $bankAccount = array_merge($bank, $bankOwner);
        } else {
            $bankAccount = null;
        }

        # todo get url avatar
        // $path = Storage::path($this->avatar);
        # end

        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'partner' => $partnerResult,
            'bank_account' => $bankAccount,
            'avatar' => $this->avatar ?? null
        ];
    }
}
