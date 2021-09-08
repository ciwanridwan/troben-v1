<?php

namespace App\Http\Resources\Api\Partner\Owner\Balance;

use App\Models\Partners\Balance\History;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoryResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var History $this */
        return [
            'amount' => $this->balance,
            'date' => $this->created_at->format('Y-m-d H:i:s'),
            'description' => $this->description,
            'package_code' => $this->package->code->content,
            'type' => $this->type,
        ];
    }
}
