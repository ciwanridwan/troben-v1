<?php

namespace App\Http\Resources\Api\Partner\Owner\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class ListManifestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        $sla = [
            'level' => 1,
            'is_late' => true,
            'deadline_at' => '2023-03-16 18:00:00',
            'late_at' => '2023-03-16 16:52:40',
            'done_at' => '2023-03-16 16:52:40'
        ];

        return [
            'type' => $this->type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'hash' => $this->hash,
            'code' => $this->code ? $this->code->only('content') : null,
            'partner' => $this->partner ? $this->partner->only('code', 'name', 'hash') : null,
            'origin_partner' => $this->origin_partner ? $this->origin_partner->only('code', 'name', 'hash') : null,
            'transporter' => $this->transporter ? $this->transporter->only('hash', 'type', 'registration_name') : null,
            'sla' => $sla
        ];
    }
}
