<?php

namespace App\Http\Resources\Api\Partner\Owner\Balance;

use Illuminate\Http\Resources\Json\JsonResource;

class ReportPartnerTransporterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $detail = [
            'package_code' => $this->resource['package_code'],
            'amount' => intval($this->resource['total_amount']),
            'weight' => intval($this->resource['weight']),
            'date' => $this->resource['date'],
            'type' => $this->resource['type'],
            'description' => $this->resource['description'],
        ];
        $detail = [$detail];

        return [
            'package_code' => $this->resource['package_code'],
            'total_amount' => intval($this->resource['total_amount']),
            'created_at' => $this->resource['date'],
            'detail' => $detail
        ];
    }
}
