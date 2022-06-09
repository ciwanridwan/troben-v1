<?php

namespace App\Http\Resources\Account;

use App\Models\Offices\Office;
use Illuminate\Http\Resources\Json\JsonResource;

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
        /** @var Office $this */
        $data = [
            'guard' => 'office',
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'role' => $this->role->name,
        ];

        return $data;
    }
}
