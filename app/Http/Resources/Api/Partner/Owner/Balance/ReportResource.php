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
        /** @var PartnerBalanceReport $this */
        return [
            'package_code' => $this->package_code,
            'total_amount' => $this->balance,
            'created_at' => $this->package_created_at,
            'detail' => HistoryResource::collection($this->balanceHistories)
        ];
    }
}
