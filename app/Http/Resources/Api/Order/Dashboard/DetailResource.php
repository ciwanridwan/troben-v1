<?php

namespace App\Http\Resources\Api\Order\Dashboard;

use App\Models\Packages\Package;
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
        $manifest = $this->deliveries->where('type', 'pickup')->first();

        if ($this->multiDestination()->exists()) {
            $orderType = 'Multi';
            $packageChild = $this->getMultiChildPackages($manifest, $orderType);
        } else {
            $orderType = 'Single';
            $packageChild = [];
        }

        if (substr($this->sender_phone, 0, 3) === '+62') {
            $senderPhone = str_replace('+62', '0', $this->sender_phone);
        } else {
            $senderPhone = $this->sender_phone;
        }

        if (substr($this->receiver_phone, 0, 3) === '+62') {
            $receiverPhone = str_replace('+62', '0', $this->receiver_phone);
        } else {
            $receiverPhone = $this->receiver_phone;
        }

        $data = [
            'id' => $this->id,
            'hash' => $manifest ? $manifest->hash : null, // inject hash delivery request from frontend team
            'package_hash' => $this->hash,
            'service_code' => $this->service_code,
            'transporter_type' => $this->transporter_type,
            'order_type' => $orderType,
            'sender_name' => $this->sender_name,
            'sender_address' => $this->sender_address,
            'sender_detail_address' => $this->sender_way_point,
            'sender_phone' => $senderPhone,
            'receiver_name' => $this->receiver_name,
            'receiver_address' => $this->receiver_address,
            'receiver_detail_address' => $this->receiver_way_point,
            'receiver_phone' => $receiverPhone,
            'tier_price' => $this->tier_price,
            'estimation_notes' => $this->getNotes($this->resource),
            'origin_address' => [
                'province' => $this->origin_regency ? $this->origin_regency->province->name : null,
                'regency' => $this->origin_regency ? $this->origin_regency->name : null
            ],
            'destination_id' => $this->destination_sub_district ? $this->destination_sub_district->id : null, // sub_district_id
            // before destination_address
            'destination_address' => [
                'province' => $this->destination_regency ? $this->destination_regency->province->name : null,
                'province_id' => $this->destination_regency ? $this->destination_regency->province->id : null,
                'regency' => $this->destination_regency ? $this->destination_regency->name : null,
                'regency_id' => $this->destination_regency ? $this->destination_regency->id : null,
                'district' => $this->destination_district ? $this->destination_district->name : null,
                'district_id' => $this->destination_district ? $this->destination_district->id : null,
                'sub_district' => $this->destination_sub_district ? $this->destination_sub_district->name : null,
                'sub_district_id' => $this->destination_sub_district ? $this->destination_sub_district->id : null
            ],
            'items' => $this->items ? $this->items->map(function ($q) {
                $packings = [];
                if (!is_null($q->handling)) {
                    foreach ($q->handling as $handling) {
                        $packing = $handling['type'];
                        array_push($packings, $packing);
                    }
                }

                $result = [
                    'hash' => $q->hash,
                    'name' => $q->name ?? '',
                    'desc' => $q->desc ?? '',
                    'qty' => $q->qty,
                    'is_insured' => $q->is_insured,
                    'weight' => $q->weight,
                    'height' => $q->height,
                    'length' => $q->length,
                    'width' => $q->width,
                    'price' => $q->price,
                    'weight_borne_volume' => $q->weight_volume,
                    'weight_borne_total' => $q->weight_borne_total,
                    'handling' => $packings,
                    'category_name' => $q->categories ? $q->categories->name : '',
                    'category_item_id' => $q->categories ? $q->categories->id : 0,
                    'is_glassware' => $q->is_glassware
                ];
                return $result;
            }) : null,
            'images' => $this->attachments ? $this->attachments->map(function ($q) {
                $result = [
                    'id' => $q->id,
                    'uri' => $q->uri
                ];
                return $result;
            }) : null,
            'calculate_data' => $this->getPrices($this->resource),
            // 'package_child' => $packageChild ?? null,
        ];

        if (isset($packageChild)) {
            $data['package_child'] = array_merge([$data], $packageChild);
        } else {
            $data['package_child'] = [$data];
        }
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

    private function getPrices($package): array
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
        $pickup = $this->prices()->where('type', PackagesPrice::TYPE_DELIVERY)->where('description', PackagesPrice::TYPE_PICKUP)->first();

        $totalAmount = ($insurance ?? 0) + ($handling ?? 0) + ($additional ? $additional->amount : 0) + ($service ? $service->amount : 0) + ($pickup ? $pickup->amount : 0);

        return [
            'insurance_price_total' => (int) $insurance ?? 0,
            'handling' => (int) $handling ?? 0,
            'additional_price' => $additional ? $additional->amount : 0,
            'service' => $service ? $service->amount : 0,
            'pickup' => $pickup ? $pickup->amount : 0,
            'total_amount' => $totalAmount
        ];
    }

    private function getMultiChildPackages($manifest, $orderType): array
    {
        $childId = $this->multiDestination->pluck('child_id')->toArray();
        $packageChild = Package::query()->whereIn('id', $childId)->get()->map(function ($q) use ($manifest, $orderType) {
            if (substr($q->sender_phone, 0, 3) === '+62') {
                $senderPhone = str_replace('+62', '0', $q->sender_phone);
            } else {
                $senderPhone = $q->sender_phone;
            }

            if (substr($q->receiver_phone, 0, 3) === '+62') {
                $receiverPhone = str_replace('+62', '0', $q->receiver_phone);
            } else {
                $receiverPhone = $q->receiver_phone;
            }
            $data = [
                'id' => $q->id,
                'hash' => $manifest ? $manifest->hash : null, // inject hash delivery request from frontend team
                'package_hash' => $q->hash,
                'service_code' => $q->service_code,
                'transporter_type' => $q->transporter_type,
                'order_type' => $orderType,
                'sender_name' => $q->sender_name,
                'sender_address' => $q->sender_address,
                'sender_detail_address' => $q->sender_way_point,
                'sender_phone' => $senderPhone,
                'receiver_name' => $q->receiver_name,
                'receiver_address' => $q->receiver_address,
                'receiver_detail_address' => $q->receiver_way_point,
                'receiver_phone' => $receiverPhone,
                'tier_price' => $q->tier_price,
                'estimation_notes' => $this->getNotes($q),
                'origin_address' => [
                    'province' => $q->origin_regency ? $q->origin_regency->province->name : null,
                    'regency' => $q->origin_regency ? $q->origin_regency->name : null
                ],
                'destination_id' => $q->destination_sub_district ? $q->destination_sub_district->id : null, // sub_district_id
                // before destination_address
                'destination_address' => [
                    'province' => $q->destination_regency ? $q->destination_regency->province->name : null,
                    'province_id' => $q->destination_regency ? $q->destination_regency->province->id : null,
                    'regency' => $q->destination_regency ? $q->destination_regency->name : null,
                    'regency_id' => $q->destination_regency ? $q->destination_regency->id : null,
                    'district' => $q->destination_district ? $q->destination_district->name : null,
                    'district_id' => $q->destination_district ? $q->destination_district->id : null,
                    'sub_district' => $q->destination_sub_district ? $q->destination_sub_district->name : null,
                    'sub_district_id' => $q->destination_sub_district ? $q->destination_sub_district->id : null
                ],
                'items' => $q->items ? $q->items->map(function ($i) {
                    $packings = [];
                    foreach ($i->handling as $handling) {
                        $packing = $handling['type'];
                        array_push($packings, $packing);
                    }
                    $result = [
                        'hash' => $i->hash,
                        'name' => $i->name ?? '',
                        'desc' => $i->desc ?? '',
                        'qty' => $i->qty,
                        'is_insured' => $i->is_insured,
                        'weight' => $i->weight,
                        'height' => $i->height,
                        'length' => $i->length,
                        'width' => $i->width,
                        'price' => $i->price,
                        'weight_borne_volume' => $i->weight_volume,
                        'weight_borne_total' => $i->weight_borne_total,
                        'handling' => $packings,
                        'category_name' => $i->categories ? $i->categories->name : '',
                        'category_item_id' => $i->categories ? $i->categories->id : 0,
                        'is_glassware' => $i->is_glassware
                    ];
                    return $result;
                }) : null,
                'images' => $q->attachments ? $q->attachments->map(function ($a) {
                    $result = [
                        'id' => $a->id,
                        'uri' => $a->uri
                    ];
                    return $result;
                }) : null,
                'calculate_data' => $this->getPrices($q)
            ];

            return $data;
        })->values()->toArray();

        return $packageChild;
    }
}
