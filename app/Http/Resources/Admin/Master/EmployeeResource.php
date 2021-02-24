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
        $data = [];
        foreach ($this->users as $key => $user) {
            $data[] = array_merge([
                'partner_type' => $this->type,
                'partner_code' => $this->code,
            ], $user->toArray());
        }

        return $data;
    }
}
