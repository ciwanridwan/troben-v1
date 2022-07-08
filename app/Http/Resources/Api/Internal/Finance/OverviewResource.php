<?php

namespace App\Http\Resources\Api\Internal\Finance;

use Illuminate\Http\Resources\Json\JsonResource;

class OverviewResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'mitra_count' => $this['mitra_count'],
            'request_count' => $this['request_count'],
        ];


        return $data;
    }
}
