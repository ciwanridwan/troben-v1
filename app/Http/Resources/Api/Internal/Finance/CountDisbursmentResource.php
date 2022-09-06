<?php

namespace App\Http\Resources\Api\Internal\Finance;

use App\Models\Payments\Withdrawal;
use Illuminate\Http\Resources\Json\JsonResource;

class CountDisbursmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $result = Withdrawal::query()->where('status', Withdrawal::STATUS_REQUESTED)->count();

        $data = [
            'count' => $result
        ];

        return $data;
    }
}
