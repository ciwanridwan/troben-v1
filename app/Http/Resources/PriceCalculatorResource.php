<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class PriceCalculatorResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'price' => PriceResource::make($this->resource['price']),
            'actual_property' => $this->resource['actual_property'],
            'charge' => [
                'tier' => $this->resource['tier'],
                'weight' => $this->resource['weight'],
            ],
        ];

        if (Arr::has($this->resource, 'dimension')) {
            $data['charge']['dimension'] = $this->resource['dimension'];
        }

        if (Arr::has($this->resource, 'insurance')) {
            $data['charge']['insurance'] = $this->resource['insurance'];
        }

        if (Arr::has($this->resource, 'packaging')) {
            $data['charge']['packaging'] = $this->resource['packaging'];
        }


        return $data;
    }
}
