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
            'package_code' => $this->package_code,
            'amount' => intval($this->total_amount),
            'weight' => intval($this->total_weight),
            'date' => $this->created_at,
            'type' => $this->type,
            'description' => $this->description,
        ];
        $detail = [$detail];

        return [
            'package_code' => $this->package_code,
            'total_amount' => intval($this->total_amount),
            'created_at' => $this->created_at,
            'detail' => $detail
        ];
    }
}
