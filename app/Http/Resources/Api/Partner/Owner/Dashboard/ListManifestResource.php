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
        if (!is_null($this->sla)) {
            if (!is_null($this->sla->reached_at) && $this->sla->reached_at > $this->sla->deadline) {
                $isLate = true;
                $lateAt = $this->sla->reached_at;
            } else {
                $isLate = false;
                $lateAt = null;
            }
        } else {
            $isLate = false;
            $lateAt = null;
        }

        $sla = [
            'level' => $this->sla ? $this->sla->level : null,
            'is_late' => $isLate,
            'deadline_at' => $this->sla ? $this->sla->deadline : null,
            'late_at' =>  $lateAt,
            'done_at' => $this->sla ? $this->sla->reached_at : null
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
