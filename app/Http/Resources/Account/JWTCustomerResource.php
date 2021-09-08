<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Resources\Json\JsonResource;

class JWTCustomerResource extends JsonResource
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
        if (! is_null($this->google_id)) {
            $data = [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'avatar' => $this->attachments()->first()->uri ?? null,
                'type' => 'google',
            ];
        } elseif (! is_null($this->facebook_id)) {
            $data = [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'avatar' => $this->attachments()->first()->uri ?? null,
                'type' => 'facebook',
            ];
        } else {
            $data = [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'avatar' => $this->attachments()->first()->uri ?? null,
                'type' => 'trawlbens',
            ];
        }


        $address = $this->addresses;

        if ($address->count() > 0) {
            $data['address'] = ($address->where('is_default', true)->first()->only('address'))['address'];
        }

        return $data;
    }
}
