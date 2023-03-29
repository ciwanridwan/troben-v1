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

        // set dummy sla
        $sla = [
            'level' => 1,
            'is_late' => true,
            'deadline_at' => '2023-03-16 18:00:00',
            'late_at' => '2023-03-16 16:52:40',
            'done_at' => '2023-03-16 16:52:40'
        ];

        return [
            'created_at' => $this->created_at->format('y-m-d H:i:s'),
            'code' => $this->code ? $this->code->content : null,
            'employee_name' => $this->estimator ? $this->estimator->name : null,
            'status' => $status,
            'sla' => $sla,
        ];
    }
}
