<?php

namespace App\Http\Resources\Account;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var \App\Models\User|\App\Models\Customers\Customer $this */
        $data = [
            'hash' => (string) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'username' => $this->username,
            'address' => $this->address,
            'referral_code' => $this->referral_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_active' => $this->is_active,
            'avatar' => $this->attachments()->first()->uri ?? null,
        ];

        if ($this->resource instanceof User) {
            /** @var \Illuminate\Database\Eloquent\Collection $partners */
            $partners = $this->resource->partners;
            // dd($partners);

            $data['partner'] = null;
            if ($partners->count() > 0) {
                $data['partner'] = $partners->first()->only(['name', 'code', 'type', 'address',  'latitude',  'longitude']);
                $data['partner']['as'] = $partners
                    ->where('code', Arr::get($data, 'partner.code'))
                    ->pluck('pivot')->map->role->toArray();
            }
            $data['bankOwner'] = null;
            if ($this->resource->bankOwner) {
                $data['bankOwner'] = $this->resource->bankOwner;
                $data['bankOwner']['bank'] = $this->resource->BankOwner->banks;
            }

            $transporters = $this->resource->transporters;

            $data['vehicle'] = null;
            if ($transporters->count() > 0) {
                $data['vehicle'] = $transporters->first()->only(['type', 'registration_name', 'registration_number', 'registration_year']);
            }
        }

        return $data;
    }
}
