<?php

namespace App\Http\Resources\Api\Order\Dashboard;

use App\Models\Packages\Price as PackagesPrice;
use App\Models\Price;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($this->multiDestination()->exists()) {
            $orderType = 'Multi';
        } else {
            $orderType = 'Single';
        }

        $data = [
            'id' => $this->id,
            'hash' => $this->hash,
            'service_code' => $this->service_code,
            'transporter_type' => $this->transporter_type,
            'order_type' => $orderType,
            'sender_name' => $this->sender_name,
            'sender_address' => $this->sender_address,
            'sender_detail_address' => $this->sender_way_point,
            'sender_phone' => $this->sender_phone,
            'receiver_name' => $this->receiver_name,
            'receiver_address' => $this->receiver_address,
            'receiver_detail_address' => $this->receiver_way_point,
            'receiver_phone' => $this->receiver_phone,
            'tier_price' => $this->tier_price,
            'estimation_notes' => $this->getNotes($this->resource),
            'origin_address' => [
                'province' => $this->origin_regency ? $this->origin_regency->province->name : null,
                'regency' => $this->origin_regency ? $this->origin_regency->name : null
            ],
            'destination_address' => [
                'province' => $this->destination_regency ? $this->destination_regency->province->name : null,
                'regency' => $this->destination_regency ? $this->destination_regency->name : null,
                'district' => $this->destination_district ? $this->destination_district->name : null,
                'sub_district' => $this->destination_sub_district ? $this->destination_sub_district->name : null
            ],
            'items' => $this->items ? $this->items->map(function ($q) {
                $result = [
                    'name' => $q->name,
                    'desc' => $q->desc,
                    'is_insured' => $q->is_insured,
                    'weight' => $q->weight,
                    'height' => $q->height,
                    'length' => $q->length,
                    'width' => $q->width,
                    'insurance_price' => $q->price,
                    'weight_borne_total' => $q->weight_borne_total,
                    'handling' => $q->handling,
                    'category_name' => $q->categories ? $q->categories->name : null
                ];
                return $result;
            }) : null,
            'attachments' => $this->attachments ? $this->attachments->map(function ($q) {
                $result = [
                    'id' => $q->id,
                    'uri' => $q->uri
                ];
                return $result;
            }) : null,
            'prices' => $this->getPrices()
        ];

        return $data;
    }

    private function getNotes($package): string
    {
        $price = Price::query()
            ->where('origin_regency_id', $package->origin_regency_id)
            ->where('destination_id', $package->destination_sub_district_id)
            ->first();

        if ($price) {
            return $price->notes;
        } else {
            return 'Estimasi Pengiriman Tidak Terjangkau';
        }
    }

    private function getPrices(): array
    {
        $insurance = $this->prices()->where('type', PackagesPrice::TYPE_INSURANCE)->where('description', PackagesPrice::TYPE_INSURANCE)->sum('amount');
        $handling = $this->prices()->where('type', PackagesPrice::TYPE_HANDLING)->sum('amount');
        $service = $this->prices()->where('type', PackagesPrice::TYPE_SERVICE)->where(function ($q) {
            $q->where('description', PackagesPrice::TYPE_SERVICE)
            ->orWhere('description', PackagesPrice::DESCRIPTION_TYPE_EXPRESS)
            ->orWhere('description', PackagesPrice::DESCRIPTION_TYPE_CUBIC)
            ->orWhere('description', PackagesPrice::DESCRIPTION_TYPE_BIKE);
        })->first();
        $additional = $this->prices()->where('type', PackagesPrice::TYPE_SERVICE)->where('description', PackagesPrice::TYPE_ADDITIONAL)->first();

        $totalAmount = ($insurance ?? 0) + ($handling ?? 0) + ($additional ? $additional->amount : 0) + ($service ? $service->amount : 0);

        return [
            'insurance' => (int) $insurance ?? 0,
            'packing' => (int) $handling ?? 0,
            'additional' => $additional ? $additional->amount : 0,
            'service' => $service ? $service->amount : 0,
            'total_amount' => $totalAmount
        ];
    }
}
