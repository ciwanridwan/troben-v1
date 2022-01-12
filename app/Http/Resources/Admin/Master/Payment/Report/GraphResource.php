<?php

namespace App\Http\Resources\Admin\Master\Payment\Report;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class GraphResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [];
        if (! empty($this->resource['date'])) {
            $days = Carbon::parse($this->resource['date'])->daysInMonth;
        } else {
            $days = Carbon::today()->daysInMonth;
        }

        for ($i = 1; $i <= $days; $i++) {
            $record = $this->resource['data']->firstWhere('created_at_day', $i);
            $data[$i] = ! is_null($record) ? $record['balance'] : 0;
        }

        return $data;
    }
}
