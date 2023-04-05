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
        $sla = [
            'level' => 1,
            'is_late' => true,
            'deadline_at' => '2023-03-16 18:00:00',
            'late_at' => '2023-03-16 16:52:40',
            'done_at' => '2023-03-16 16:52:40'
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
