<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CodeLogResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            "id" => $this->resource->id,
            "code_id" => $this->resource->code_id,
            "code_logable_type" => $this->resource->code_logable_type,
            "code_logable_id" => $this->resource->code_logable_id,
            "type" => $this->resource->type,
            "showable" => $this->resource->showable,
            "status" => $this->resource->status,
            "description" => $this->resource->description,
            "created_at" => $this->resource->created_at->format('d-m-Y'),
            "updated_at" => $this->resource->updated_at,
            "deleted_at" => $this->resource->deleted_at
        ];
    }
}
