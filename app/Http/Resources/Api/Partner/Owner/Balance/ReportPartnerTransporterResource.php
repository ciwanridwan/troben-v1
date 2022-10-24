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
            'amount' => $this->balance,
            'date' => $this->created_at->format('Y-m-d h:i:s'),
            'description' => $this->description,
            'package_code' => $this->deliveries->code->content,
            'type' => $this->type,
            'weight' => $this->deliveries->packages->first()->total_weight
        ];
        $detail = array($detail);

        return [
            'package_code' => $this->deliveries->code->content,
            'total_amount' => $this->balance,
            'created_at' => $this->created_at->format('Y-m-d h:i:s'),
            'detail' => $detail
        ];
    }
}
