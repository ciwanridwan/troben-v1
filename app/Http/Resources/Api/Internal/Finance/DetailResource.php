<?php

namespace App\Http\Resources\Api\Internal\Finance;
use App\Models\Partners\Partner;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    public function toArray($request)
    {

        /** @var \App\Models\Payments\Withdrawal */
        $data = [
            'id' => $this['id'],
            'hash' => $this['hash'],
            'created_at' => $this['created_at']->format('Y-m-d'),
            'status' => $this['status'],
            'packages' => $this->packages->load('code')
        ];

        return $data;
    }
}
