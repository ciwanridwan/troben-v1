<?php

namespace App\Http\Resources\Api\Partner\Owner\Balance;

use App\Models\Partners\Balance\History;
use App\Models\View\PartnerBalanceReport;
use Illuminate\Http\Resources\Json\JsonResource;
use function PHPUnit\Framework\isInstanceOf;

class HistoryResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var History|PartnerBalanceReport $this */
        return [
            'amount' => $this->balance,
            'date' => isInstanceOf(PartnerBalanceReport::class) ? $this->history_created_at : $this->created_at->format('Y-m-d H:i:s'),
            'description' => $this->description,
            'package_code' => isInstanceOf(PartnerBalanceReport::class) ? $this->package_code : $this->package->code->content,
            'type' => $this->type,
        ];
    }
}
