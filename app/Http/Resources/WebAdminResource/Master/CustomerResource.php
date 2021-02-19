<?php

namespace App\Http\Resources\WebAdminResource\Master;

use App\Http\Resources\Account\CustomerResource as AccountCustomerResource;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = AccountCustomerResource::make($this->resource)->toArray($request);
        $data['orders'] = [
            'count' => $this->orders()->paid()->count(),
            'total_payment' => $this->orders()->paid()->sum('total_payment')
        ];
        return $data;
    }
}
