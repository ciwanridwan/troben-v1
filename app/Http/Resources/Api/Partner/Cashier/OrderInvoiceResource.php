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
        if ($this->multiDestination()->exists()) {
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
            'status_label' => $this->getMessagesFromStatus($this->status),
            'created_at' => $this->created_at->format('d-m-Y'),
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
        switch (true) {
            case $status === Package::STATUS_CANCEL:
                $message = 'Pesanan dibatalkan';
                break;
            case $status === Package::CREATED_AT:
                $message = 'Pesanan dibuat';
                break;
            case $status === Package::STATUS_PENDING:
                $message = 'Menunggu driver ditugaskan';
                break;
            case $status === Package::STATUS_WAITING_FOR_PICKUP:
                $message = 'Pesanan menunggu proses penjemputan';
                break;
            case $status === Package::STATUS_PICKED_UP:
                $message = 'Pesanan dalam proses penjemputan';
                break;
            case $status === Package::STATUS_ESTIMATING:
                $message = 'Proses ukur timbang oleh gudang';
                break;
            case $status === Package::STATUS_ESTIMATED:
                $message = 'Selesai diukur timbang oleh gudang';
                break;
            case $status === Package::STATUS_WAITING_FOR_APPROVAL:
                $message = 'Menunggu konfirmasi customer';
                break;
            case $status === Package::STATUS_REVAMP:
                $message = 'Revisi';
                break;
            case $status === Package::STATUS_ACCEPTED:
                $message = 'Pesanan diterima oleh mitra';
            case $status === Package::STATUS_WAITING_FOR_PAYMENT:
                $message = 'Menunggu pembayaran oleh customer';
                break;
            case $status === Package::STATUS_WAITING_FOR_PACKING:
                $message = 'Menunggu pesanan dikemas';
                break;
            case $status === Package::STATUS_PACKING:
                $message = 'Proses kemas oleh mitra';
                break;
            case $status === Package::STATUS_PACKED:
                $message = 'Pesanan telah dikemas oleh mitra';
                break;
            case $status === Package::STATUS_MANIFESTED:
                $message = 'Pesanan telah masuk dalam manifest';
                break;
            case $status === Package::STATUS_IN_TRANSIT:
                $message = 'Pesanan dalam perjalanan';
                break;
            case $status === Package::STATUS_WITH_COURIER:
                $message = 'Pesanan dalam pengantaran kurir';
                break;
            case $status === Package::STATUS_PACKED:
                $message = 'Pesanan telah sampai tujuan';
                break;
            default:
                $message = 'Status pesanan tidak diketahui, segera lapor IT Team';
                break;
        }

    return $message;
    }
}
