<?php

namespace App\Http\Resources\Api\Order\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($this->multiDestination()->exists()) {
            $orderType = 'Multi';
        } else {
            $orderType = 'Single';
        }

        $data = [
            'id' => $this->id,
            'hash' => $this->hash,
            'service_code' => $this->service_code,
            'transporter_type' => $this->transporter_type,
            'order_type' => $orderType,
            'sender_name' => $this->sender_name,
            'sender_address' => $this->sender_address,
            'sender_phone' => $this->sender_phone,
            'receiver_name' => $this->receiver_name,
            'receiver_address' => $this->receiver_address,
            'receiver_phone' => $this->receiver_phone,
            'origin_address' => [
                'province' => $this->origin_regency ? $this->origin_regency->province->name : null,
                'regency' => $this->origin_regency ? $this->origin_regency->name : null
            ],
            'destination_address' => [
                'province' => $this->destination_regency ? $this->destination_regency->province->name : null,
                'regency' => $this->destination_regency ? $this->destination_regency->name : null,
                'district' => $this->destination_district ? $this->destination_district->name : null,
                'sub_district' => $this->destination_sub_district ? $this->destination_sub_district->name : null
            ],
            'items' => $this->items ? $this->items->map(function ($q) {
                $result = [
                    'name' => $q->name,
                    'desc' => $q->desc,
                    'is_insured' => $q->is_insured,
                    'weight_borne_total' => $q->weight_borne_total,
                    'handling' => $q->handling
                ];
                return $result;
            }) : null,
            'attachments' => $this->attachments ? $this->attachments->map(function ($q) {
                $result = [
                    'id' => $q->id,
                    'uri' => $q->uri
                ];
                return $result;
            }) : null
        ];

        return $data;
    }
}
