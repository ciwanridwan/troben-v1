<?php

namespace App\Http\Resources\Api\Assignable;

use Illuminate\Http\Resources\Json\JsonResource;

class DriverTransporterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $transporter = $this->userable;
        unset($this->userable);

        return [
            'user_id' => $this->user_id,
            'userable_id' => $this->userable_id,
            'role' => $this->role,
            'hash' => $this->hash,
            'user' => $this->user->only('name', 'hash'),
            'transporter' => $transporter->only('type', 'hash', 'registration_number')
        ];
    }
}
