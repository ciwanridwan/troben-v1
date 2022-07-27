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
        /**TODO SELECTED COLUMN FOR SHOW TO RESPONSE */
        $partner = Partner::find($this->partner_id)->only('id', 'name', 'code', 'balance');
        /**END TODO */

        /** @var \App\Models\Payments\Withdrawal $this */
        $data = [
            'hash' => $this->hash,
            'user' => $partner,
            'status' => $this->status,
            'amount' => $this->amount,
            'created_at' => date('Y-m-d h:i:s', strtotime($this->created_at)),
            // 'first_balance' => $this->first_balance,
            // 'updated_at' => date('Y-m-d h:i:s', strtotime($this->updated_at)),
        ];

        return $data;
    }
}
