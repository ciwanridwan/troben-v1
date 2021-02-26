<?php

namespace App\Http\Resources\Api\Partner\Asset;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class TransporterResource extends JsonResource
{
    protected array $data = array();
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        foreach ($this->resource as $transporter) {
            $this->data[] = Arr::only($transporter->toArray(),['name','registration_number','is_verified','type']);
        }

        return $this->data;
    }
}
