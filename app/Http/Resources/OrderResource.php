<?php

namespace App\Http\Resources;

use App\Http\Resources\Account\CustomerResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'customer' => CustomerResource::make($this->customer),
            'est_payment' => $this->est_payment,
            'total_payment' => $this->total_payment,
            'payment_channel' => $this->payment_channel,
            'payment_status' => $this->payment_status,
            'status' => $this->status,
        ];
    }
}
