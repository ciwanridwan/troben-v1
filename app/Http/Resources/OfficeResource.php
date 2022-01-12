<?php

namespace App\Http\Resources;

use App\Models\Offices\Office;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Office $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'username' => $this->username,
            'address' => $this->address,
            'is_active' => $this->is_active,
            'role' => $this->role->detail->name,
            'created_at' => date('Y-m-d h:i:s', strtotime($this->created_at)),
            'updated_at' => date('Y-m-d h:i:s', strtotime($this->updated_at)),
        ];
    }
}
