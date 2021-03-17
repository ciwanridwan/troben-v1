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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ];

        if ($this->resource instanceof User) {
            /** @var \Illuminate\Database\Eloquent\Collection $partners */
            $partners = $this->resource->partners;

            if ($partners->count() > 0) {
                $data['partner'] = $partners->first()->only(['name', 'code', 'type']);
                $data['partner']['as'] = $partners
                    ->where('code', Arr::get($data, 'partner.code'))
                    ->pluck('pivot')->map->role->toArray();
            }
        }

        return $data;
    }
}
