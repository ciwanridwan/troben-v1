<?php

namespace App\Http\Resources\Api\Partner\Owner\Dashboard;

use App\Models\Payments\Withdrawal;
use Illuminate\Http\Resources\Json\JsonResource;

class ListWithdrawalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $totalAccepted = $this->amount;
        if ($this->status === Withdrawal::STATUS_REQUESTED) {
            $totalAccepted = 0;
        }

        return [
            'hash' => $this->hash,
            'created_at' => $this->created_at->format('y-m-d H:i:s'),
            'transaction_code' => $this->transaction_code,
            'request_amount' => $this->first_balance,
            'total_accepted' => $totalAccepted,
            'status' => $this->status
        ];
    }
}
