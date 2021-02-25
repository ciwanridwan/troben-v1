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
            'hash' => $this->hash,
            'name'  => $this->user->name,
            'phone' => $this->user->phone,
            'email' => $this->user->email,
            'role' => $this->role,
            'partner' => [
                'type' => $this->userable->type,
                'code' => $this->userable->code
            ]
        ];
    }
}
