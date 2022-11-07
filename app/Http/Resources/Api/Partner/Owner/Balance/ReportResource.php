<?php

namespace App\Http\Resources\Api\Partner\Owner\Balance;

use App\Models\View\PartnerBalanceReport;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        // $detail = [
        //     'package_code' => $this->package_code,
        //     'amount' => intval($this->total_amount),
        //     'weight' => intval($this->total_weight),
        //     'date' => $this->created_at,
        //     'type' => $this->type,
        //     'description' => $this->description,
        // ];
        // $detail = array($detail);

        /** @var PartnerBalanceReport $this */
        return [
            'package_code' => $this->package_code,
            'total_amount' => $this->balance,
            'created_at' => $this->package_created_at,
            'detail' => HistoryResource::collection($this->balanceHistories)
            // 'package_code' => $this->package_code,
            // 'total_amount' => intval($this->total_amount),
            // 'created_at' => $this->created_at,
            // 'detail' => $detail
        ];
    }
}
