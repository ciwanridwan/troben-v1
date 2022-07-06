<?php

namespace App\Http\Resources\Api\Internal\Finance;

use App\Models\Partners\Partner;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    public function toArray($request)
    {
        $partner = Partner::find($this->partner_id)->only('id', 'name', 'code', 'balance');

        /** @var \App\Models\Payments\Withdrawal */
        $data = [
            'id' => $this['id'],
            'hash' => $this['hash'],
            'partner_id' => $partner,
            'amount' => $this['amount'],
            'created_at' => $this['created_at'],
            'status' => $this['status']
        ];

        return $data;
    }
}
