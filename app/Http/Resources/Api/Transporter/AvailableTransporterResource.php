<?php

namespace App\Http\Resources\Api\Transporter;

use Illuminate\Http\Resources\Json\JsonResource;

class AvailableTransporterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource;
    }
}
