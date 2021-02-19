<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var \App\Models\Products\Product $this  */
        return [
            'name' => $this->name,
            'logo' => $this->logo,
            'description' => $this->description,
            'is_enabled' => $this->is_enabled,
        ];
    }
}
