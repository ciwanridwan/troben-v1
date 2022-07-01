<?php

namespace App\Http\Resources\Api\Internal\Finance;

use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'request' => $this['request'],
            'receipt_list' => $this['receipt_list'],
        ];


        return $data;
    }
}
