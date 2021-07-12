<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var \App\Models\Customers\Customer $this */
        $data = [
            'hash' => $this->hash,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'attachments' => $this->attachments()->first()
        ];

        $address = $this->addresses;

        if ($address->count() > 0) {
            $data['address'] = ($address->where('is_default', true)->first()->only('address'))['address'];
        }

        return $data;
    }
}
