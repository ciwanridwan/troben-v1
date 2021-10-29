<?php

namespace App\Http\Resources\Admin\Master\Payment\Report;

use App\Models\View\PartnerBalanceReport;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerBalanceDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        /** @var $this PartnerBalanceReport */
        return [
            'partner_code' => $this->partner_code,
            'partner_name' => $this->partner_name,
            'partner_geo_regency' => $this->partner_geo_regency,
            'partner_geo_province' => $this->partner_geo_province,
            'partner_address' => $this->partner->address,
            'balance' => $this->balance,
        ];
    }
}
