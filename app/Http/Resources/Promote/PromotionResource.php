<?php

namespace App\Http\Resources\Promote;

use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Promotion $this */
        return [
            'hash' => $this->hash,
            'title' => $this->title,
            'type' => $this->type,
            'terms_and_conditions' => $this->terms_and_conditions,
            'min_payment' => $this->min_payment,
            'max_payment' => $this->max_payment,
            'min_weight' => $this->min_weight,
            'max_weight' => $this->max_weight,
            'attachment' => $this->attachments()->first()->uri ?? null,
            'created_at' => date('Y-m-d h:i:s', strtotime($this->created_at)),
            'updated_at' => date('Y-m-d h:i:s', strtotime($this->updated_at)),
        ];
    }
}
