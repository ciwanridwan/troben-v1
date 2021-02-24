<?php

namespace App\Http\Resources\Admin\Master;

use App\Http\Resources\Account\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'partner_type' => $this->partner_type,
            'partner_code' => $this->partner_code,
            'name'  => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'role' => $this->role
        ];
    }
}
