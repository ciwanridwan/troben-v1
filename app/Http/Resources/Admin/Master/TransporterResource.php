<?php

namespace App\Http\Resources\Admin\Master;

use App\Models\Partners\Transporter;
use Illuminate\Http\Resources\Json\JsonResource;

class TransporterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $detailType = [];
        foreach (Transporter::getDetailAvailableTypes() as $item) {
            if ($item['name'] === $this->type) {
                $detailType = $item;
            }
        }

        return [
            'hash' => $this->hash,
            'production_year' => $this->production_year,
            'registration_number' => $this->registration_number,
            'registration_year' => $this->registration_year,
            'registration_name' => $this->registration_name,
            'type' => $detailType,
            'partner' => PartnerResource::make($this->partner),
            'is_verified' => $this->is_verified,
            'verified_at' => $this->verified_at,
        ];
    }
}
