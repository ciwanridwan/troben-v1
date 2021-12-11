<?php

namespace App\Http\Resources\Account;

use App\Models\Offices\Office;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class JWTOfficeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var \App\Models\Offices\Office $this */
        $data = [
            'guard' => 'office',
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,

        ];

        if ($this->resource instanceof Office) {
            /** @var \Illuminate\Database\Eloquent\Collection $partners */
//            $partners = $this->resource->partners;
//
//            if ($partners->count() > 0) {
//                $data['partner'] = $partners->first()->only(['name', 'code', 'type']);
//                $data['partner']['as'] = $partners
//                    ->where('code', Arr::get($data, 'partner.code'))
//                    ->pluck('pivot')->map->role->toArray();
//            }
        }

        return $data;
    }
}
