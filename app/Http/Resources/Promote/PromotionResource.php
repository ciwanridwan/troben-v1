<?php

namespace App\Http\Resources\Promote;

use App\Http\Resources\Geo\RegencyResource;
use App\Models\Promos\Promotion;
use Carbon\Carbon;
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
            'transporter_type' => $this->transporter_type,
            'destination_regency' => $this->destination_regency ? RegencyResource::make($this->resource->destination_regency) : null,
            'min_payment' => $this->min_payment,
            'min_weight' => $this->min_weight,
            'max_weight' => $this->max_weight,
            'attachment' => $this->attachments()->first()->uri ?? null,
            'start_at' => date('Y-m-d h:i:s', strtotime($this->start_date)),
            'expired_at' => date('Y-m-d h:i:s', strtotime($this->end_date)),
            'created_at' => date('Y-m-d h:i:s', strtotime($this->created_at)),
            'updated_at' => date('Y-m-d h:i:s', strtotime($this->updated_at)),
            'server_time' => Carbon::now()->format('Y-m-d H:i:s'),
        ];
    }
}
