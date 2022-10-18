<?php

namespace App\Http\Resources\Api\Partner\Owner\Balance;

use App\Models\Partners\Partner;
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
        $partner = $request->user()->partners()->first();
        $typePartner = $partner->type;
        switch ($typePartner) {
            case Partner::TYPE_TRANSPORTER:
                $packageCode = $this->delivery->code->content;
                break;
            default:
                $packageCode = $this->package_code;
                break;
        }
        /** @var PartnerBalanceReport $this */
        return [
            'package_code' => $packageCode,
            'total_amount' => $this->balance,
            'created_at' => $this->package_created_at,
            'detail' => HistoryResource::collection($this->balanceHistories)
        ];
    }
}
