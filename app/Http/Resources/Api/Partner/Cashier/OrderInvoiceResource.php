<?php

namespace App\Http\Resources\Api\Partner\Cashier;

use App\Models\Packages\Package;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderInvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $serviceLabel = '';
        if ($this->service_code === Service::TRAWLPACK_EXPRESS) {
            $serviceLabel = 'Express';
        } else {
            $serviceLabel = 'Regular';
        }

        $orderType = '';
        if ($this->multiDestination()->exists() || $this->parentDestination) {
            $orderType = 'Multiple';
        } else {
            $orderType = 'Single';
        }

        // $existsDate = $this->created_at->format('d-m-Y');
        // $date = Carbon::parse($existsDate)->isoFormat('D MMMM YYYY');
        return [
            'id' => $this->id,
            'hash' => $this->hash,
            'receipt_code' => $this->code ? $this->code->content : null,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'status_label' => $this->getMessagesFromStatus($this->status),
            'created_at' => $this->created_at->format('Y-m-d'),
            'order_type' => $orderType,
            'service_code' => $this->service_code,
            'service_label' => $serviceLabel,
        ];
    }

    /**
     * To get detail message of each status on packages
     */
    private function getMessagesFromStatus($status)
    {
        return Package::statusParser($status);
    }
}
