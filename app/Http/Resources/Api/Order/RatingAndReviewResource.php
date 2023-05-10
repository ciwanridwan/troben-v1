<?php

namespace App\Http\Resources\Api\Order;

use Illuminate\Http\Resources\Json\JsonResource;

class RatingAndReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'review' => $this->review
        ];
    }
}
