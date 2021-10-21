<?php

namespace App\Http\Resources\Admin\Master\Payment\Report;

use App\Models\Partners\Partner;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = array();
        foreach (Partner::getAvailableTypes() as $type) {
            $record = $this->resource['data']->firstWhere('partner_type',$type);
            $total_income = ! is_null($record) ? $record['balance'] : 0;
            $data[$type] = [
                'total_income' => $total_income
            ];
        }

        return array_merge([
            'income_now' => $this->resource['income_now'],
            'income_sub' => $this->resource['income_sub'],
            'income_difference' => $this->resource['income_now'] - $this->resource['income_sub'],
        ],$data);
    }
}
