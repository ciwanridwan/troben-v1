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
        // foreach ($this->resource as $transporter) {
        //     $isActive = false;
        //     if ($transporter->is_verified === true) {
        //         $isActive = true;
        //     }

        //     $this->data[] = [
        // 'hash' => $transporter->hash,
        // 'name' => $transporter->registration_name,
        // 'registration_number' => $transporter->registration_number,
        // 'is_active' => $isActive,
        // 'is_verified' => $transporter->is_verified,
        // 'verified_at' => $transporter->verified_at,
        // 'type' => $transporter->type,
        // 'year' => $transporter->registration_year,
        //     ];
        // }

        switch (true) {
            case $this->is_verified === true && !is_null($this->verified_at):
                $status = 'Active';
                break;
            case $this->is_verified === false && is_null($this->verified_at):
                $status = 'Request';
                break;
            default:
                $status = 'Non Active';
                break;
        }

        $this->data = [
            'hash' => $this->hash,
            'type' => $this->type,
            'registration_number' => $this->registration_number,
            'driver' => $this->drivers()->first() ? $this->drivers()->first()->name : null,
            'year' => $this->registration_year,
            'name' => $this->registration_name,
            // 'is_active' => $isActive,
            'status' => $status,
            'is_verified' => $this->is_verified,
            'verified_at' => $this->verified_at ? $this->verified_at->format('Y-m-d H:i:s') : null,
        ];

        // dd($this->is_verified);

        return $this->data;
    }
}
