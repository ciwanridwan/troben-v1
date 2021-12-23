<?php

namespace App\Http\Resources\Api\Partner\Owner;

use App\Models\Partners\Partner;
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
        /** @var \App\Models\Payments\Withdrawal $this */
        $data = [
            'hash' => $this->hash,
            'user' => Partner::find($this->partner_id),
            'first_balance' => $this->first_balance,
            'amount' => $this->amount,
            'status' => $this->status,
            'created_at' => date('Y-m-d h:i:s', strtotime($this->created_at)),
            'updated_at' => date('Y-m-d h:i:s', strtotime($this->updated_at)),
        ];

        return $data;
    }
}
