<?php

namespace App\Http\Resources\Api\Delivery;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
//    public function toArray($request)
//    {
//        return parent::toArray($request);
//    }

    public function toArray($request)
    {
        /** @var \App\Models\Partners\Partner $this */
        $data = [
            'code' => $this->code->content,
            'message' => $this->message,
            'data' => [
                'code' => $this->packages->code->content
            ]
        ];




        return $data;
    }
}
