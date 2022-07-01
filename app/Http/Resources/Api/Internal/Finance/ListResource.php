<?php

namespace App\Http\Resources\Api\Internal\Finance;

use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'list' => $this['list'],
            'page' => $this['page'],
            'total_data' => $this['total_data'],
            'total_page' => $this['total_page'],
        ];


        return $data;
    }
}
