<?php

namespace App\Http\Resources\Api\Partner\Owner;

use Illuminate\Http\Resources\Json\JsonResource;

class CheckReceiptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'code' => $this->content,
            'package' => $this->codeable->only('hash', 'sender_address', 'sender_way_point', 'sender_name', 'sender_phone', 'receiver_address', 'receiver_way_point', 'receiver_name', 'receiver_phone', 'status', 'payment_status'),
            'origin_address' => [
                'province' => $this->codeable->origin_regency->province ? $this->codeable->origin_regency->province->name : null,
                'regency' => $this->codeable->origin_regency ? $this->codeable->origin_regency->name : null,
            ],
            'destination_address' => [
                'province' => $this->codeable->destination_regency->province ? $this->codeable->destination_regency->province->name : null,
                'regency' => $this->codeable->destination_regency ? $this->codeable->destination_regency->name : null,
                'district' => $this->codeable->destination_district ? $this->codeable->destination_district->name : null,
                'sub_district' => $this->codeable->destination_sub_district ? $this->codeable->destination_sub_district->name : null,
                'zip_code' => $this->codeable->destination_sub_district ? $this->codeable->destination_sub_district->zip_code : null,
            ],
            'attachments' => $this->codeable->attachments,
            'logs' => $this->logs
        ];
    }
}
