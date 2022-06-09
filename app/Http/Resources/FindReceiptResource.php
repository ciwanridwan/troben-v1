<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FindReceiptResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'package' => $this->resource['package'],
            'track' => CodeLogResource::collection($this->resource['track'])
        ];
    }
}
