<?php

namespace App\Http\Resources\Account;

use App\Models\Customers\Customer;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        /** @var Customer $this */
        $data = [
            'hash' => (string) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'avatar' => $this->attachments()->first()->uri ?? null,
            'type' => $this->google_id ? 'google'
                : ($this->facebook_id ? 'facebook' : 'trawlbens')
        ];

        $address = $this->addresses;

        if ($address->count() > 0) {
            $data['address'] = ($address->where('is_default', true)->first()->only('address'))['address'];
        }

        return $data;
    }
}
