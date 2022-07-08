<?php

namespace App\Http\Resources\Api\Internal\Finance;

use App\Models\Payments\Withdrawal;
use Illuminate\Http\Resources\Json\JsonResource;

class CountAmountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $amount = Withdrawal::query()->sum('amount');
        $result = (int)$amount;
        
        $data = [
            'amount' => $result
        ];

        return $data;
    }
}
