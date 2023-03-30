<?php

namespace App\Http\Resources\Api\Partner\Asset;

use Illuminate\Http\Resources\Json\JsonResource;

class TransporterResource extends JsonResource
{
    protected array $data = [];
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        foreach ($this->resource as $transporter) {
            $isActive = false;
            if ($transporter->is_verified === true) {
                $isActive = true;
            }
            $this->data[] = [
                'hash' => $transporter->hash,
                'name' => $transporter->registration_name,
                'registration_number' => $transporter->registration_number,
                'is_active' => $isActive,
                'is_verified' => $transporter->is_verified,
                'verified_at' => $transporter->verified_at,
                'type' => $transporter->type,
                'year' => $transporter->registration_year,
            ];
        }

        return $this->data;
    }
}
