<?php

namespace App\Http\Resources\Api\Partner\Asset;

use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Support\Arr;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Data formatted.
     *
     * @var array
     */
    protected array $data = [];

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $role = $this->partners->pluck('pivot')->map->role->toArray();

        $this->data = [
            'hash' => $this->hash,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $role
        ];

        if ($this->transporters->isNotEmpty()) {
            $this->data['transporters'] = $this->transporters;
        }

        return $this->data;
    }
}
