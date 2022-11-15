<?php

namespace App\Http\Resources\Api\Package;

use Illuminate\Http\Resources\Json\JsonResource;

class MultiDestinationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return [
        //     'parent_id' => $this->parent_id,
        //     'child_id' => $this->child_id
        // ];
    }
}
