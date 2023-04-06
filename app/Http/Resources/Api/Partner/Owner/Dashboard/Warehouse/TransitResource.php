<?php

namespace App\Http\Resources\Api\Partner\Owner\Dashboard\Warehouse;

use Illuminate\Http\Resources\Json\JsonResource;

class TransitResource extends JsonResource
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
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'code' => $this->code ? $this->code->content : null,
            'employee_name' => $this->createdBy ? $this->createdBy->name : null,
            'destination_partner' => $this->partner ? $this->partner->code : null,
            'sla' => $sla
        ];
    }
}
