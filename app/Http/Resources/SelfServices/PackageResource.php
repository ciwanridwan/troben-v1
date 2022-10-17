<?php

namespace App\Http\Resources\SelfServices;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PackageResource.
 *
 * @property  Package $resource
 */
class PackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        // if (! $this->resource->relationLoaded('code')) {
        //     $this->resource->load('code');
        // }

        $data = [
            'content' => $this->code->content,
            'transporter_type' => $this->transporter_type,
            'sender_name' => $this->sender_name,
            'sender_address' => $this->sender_address,
            'sender_phone' => $this->sender_phone,
            'receiver_name' => $this->receiver_name,
            'receiver_address' => $this->receiver_address,
            'receiver_phone' => $this->receiver_phone,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'updated_by' => $this->updated_by_office->name,
            'updated_at' => $this->updated_at->format('Y-m-d')
        ];

        return $data;
    }
}
