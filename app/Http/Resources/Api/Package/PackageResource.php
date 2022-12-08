<?php

namespace App\Http\Resources\Api\Package;

use App\Http\Resources\Admin\Master\PartnerResource;
use App\Models\Packages\Package;
use App\Http\Resources\Geo\RegencyResource;
use App\Http\Resources\Geo\DistrictResource;
use App\Http\Resources\Geo\SubDistrictResource;
use App\Models\Payments\Payment;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PackageResource.
 *
 * @property  Package $resource
 */
class PackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (! $this->resource->relationLoaded('updated_by')) {
            $this->resource->load('updated_by');
        }
        if (! $this->resource->relationLoaded('canceled')) {
            $this->resource->load('canceled');
        }

        if (! $this->resource->relationLoaded('code')) {
            $this->resource->load('code');
        }

        if ($this->resource->relationLoaded('items')) {
            $items = ItemResource::collection($this->resource->items)->toArray($request);

            $this->resource->unsetRelation('items');
        }

        $this->resource->load('picked_up_by');
        if ($this->resource->relationLoaded('picked_up_by')) {
            $pickedUpPartner = $this->resource->picked_up_by->first();
            $this->resource->unsetRelation('picked_up_by');
        }

        if ($this->resource->relationLoaded('partner_performance')) {
            if ($this->resource->partner_performance) {
                $dataPerformance = [
                    'level' => $this->resource->partner_performance->level,
                    'deadline_at' => $this->resource->partner_performance->deadline,
                ];
            } else {
                $dataPerformance = [
                    'level' => null,
                    'deadline_at' => null,
                ];
            }
            $this->resource->unsetRelation('partner_performance');
        }
        $data = array_merge(parent::toArray($request), [
            'origin_regency' => $this->resource->origin_regency ? RegencyResource::make($this->resource->origin_regency) : null,
            'destination_regency' => $this->resource->destination_regency ? RegencyResource::make($this->resource->destination_regency) : null,
            'destination_district' => $this->resource->destination_district ? DistrictResource::make($this->resource->destination_district) : null,
            'destination_sub_district' => SubDistrictResource::make($this->resource->destination_sub_district),
        ]);

        if (! empty($dataPerformance)) {
            $data = array_merge($data, $dataPerformance);
        }

        if (isset($pickedUpPartner)) {
            $data['picked_up_by'] = $pickedUpPartner->partner ? PartnerResource::make($pickedUpPartner->partner) : null;
        }

        if (isset($items)) {
            $data['items'] = $items;
        }

        if (! $this->resource->motoBikes()) {
            $this->resource->load('motoBikes');
        }

        /**Set type bike or item */
        if (isset($data['moto_bikes']) && $data['moto_bikes'] !== null) {
            $data['type'] = 'bike';
        } else {
            $data['type'] = 'item';
        }
        $checkIfPaymentHasGenerate = Payment::with('gateway')->where('payable_type', Package::class)
            ->where('payable_id', $data['id'])
            ->where('service_type', 'pay')
            ->where('status', ['pending', 'success'])
            ->first() ?? null;

        $isMulti = false;
        $isMultiChild = false;
        $parentHash = null;
        $childHash = [];
        $isMultiApprove = false;

        if ($this->resource->multiDestination->count()) {
            if ($this->payment_status !== Package::PAYMENT_STATUS_PAID) {
                $isMulti = true;
                $isMultiChild = true;

                if ($this->status === Package::STATUS_WAITING_FOR_APPROVAL) {
                    $isMultiApprove = $this->checkApproveForPackageMulti();
                }
            }

            $childHash = $this->resource->multiDestination->map(function ($r) {
                return [
                    'package_hash' => Package::idToHash($r['child_id'])
                ];
            })->toArray();
        }
        if (! is_null($this->resource->parentDestination)) {
            if ($this->payment_status !== Package::PAYMENT_STATUS_PAID) {
                $isMulti = true;
            }

            $parentHash = Package::idToHash($this->resource->parentDestination->parent_id);
        }

        /**New script for response */
        $result = [
            'hash' => $data['hash'],
            'created_at' => $data['created_at'],
            'content' => $data['code']['content'],
            'origin_regency' => $data['origin_regency']['name'],
            'destination_regency' => $data['destination_regency']['name'],
            'status' => $data['status'],
            'status_payment' => $data['payment_status'],
            'type' => $data['type'],
            'has_generate_payment' => $checkIfPaymentHasGenerate,
            'has_cancel' => $data['canceled'],
            'picked_up_by' => null,
            'is_multi' => $isMulti,
            'is_multi_child' => $isMultiChild,
            'multi_hash' => $parentHash,
            'multi_hash_child' => $childHash,
            'is_multi_approve' => $isMultiApprove,
        ];

        if (isset($data['picked_up_by'])) {
            $result['picked_up_by'] = [
                'code' => $data['picked_up_by']['code'],
                'contact_email' => $data['picked_up_by']['contact_email'],
                'contact_phone' => $data['picked_up_by']['contact_phone'],
                'address' => $data['picked_up_by']['address'],
            ];
        }

        return $result;
    }


    private function checkApproveForPackageMulti()
    {
        $canApprove = false;
        $childId = $this->resource->multiDestination->map(function ($q) {
           return $q->child_id;
        })->toArray();

        $check = Package::whereIn('id', $childId)->get()->filter(function ($q) {
            if ($q->status === Package::STATUS_WAITING_FOR_APPROVAL) {
                return false;
            }
            return true;
        });
        if ($check->isEmpty()) {
            $canApprove = true;
        }

        return $canApprove;
    }
}
