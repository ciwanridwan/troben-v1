<?php

namespace App\Http\Resources\Api\Partner\Owner\Dashboard\Warehouse;

use App\Models\Packages\Package;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
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
        $status = 'Selesai';
        if ($this->status === Package::STATUS_PACKING) {
            $status = 'Belum Selesai';
        }

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
            'employee_name' => $this->estimator ? $this->estimator->name : null,
            'status' => $status,
            'sla' => $sla,
        ];
    }
}
