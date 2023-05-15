<?php

namespace App\Http\Resources\Api\Partner\Owner\Balance;

use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'balance' => $this->partners()->first() ? (int)$this->partners()->first()->balance : null,
            'bank_name' => $this->bankOwner ? ($this->bankOwner->banks ? $this->bankOwner->banks->name : null) : null,
            'account_number' => $this->bankOwner ? $this->bankOwner->account_number : null,
            'account_name' => $this->bankOwner ? $this->bankOwner->account_name : null
        ];
    }
}
