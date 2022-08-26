<?php

namespace App\Http\Resources\Api\Internal\Finance;

use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    public function toArray($request)
    {
        // dd($this->resource);
        /** @var \App\Models\Payments\Withdrawal */
        $data = [
            'id' => $this['id'],
            'hash' => $this['hash'],
            'created_at' => $this['created_at']->format('Y-m-d'),
            'status' => $this['status'],
            // 'packages' => $this->packages->load('code')
        ];

        return $data;
    }
}
