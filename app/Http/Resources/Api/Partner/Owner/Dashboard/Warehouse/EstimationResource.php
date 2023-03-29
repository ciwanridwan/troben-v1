<?php

namespace App\Http\Resources\Api\Partner\Owner\Dashboard\Warehouse;

use App\Models\Packages\Package;
use Illuminate\Http\Resources\Json\JsonResource;

class EstimationResource extends JsonResource
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
        if ($this->status === Package::STATUS_WAITING_FOR_ESTIMATING || $this->status === Package::STATUS_ESTIMATING) {
            $status = 'Belum Selesai';
        }

        return [
            'created_at' => $this->created_at->format('y-m-d H:i:s'),
            'code' => $this->code ? $this->code->content : null,
            'employee_name' => $this->estimator ? $this->estimator->name : null,
            'status' => $status
        ];
    }
}
