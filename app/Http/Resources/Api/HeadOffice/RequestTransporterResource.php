<?php

namespace App\Http\Resources\Api\HeadOffice;

use App\Models\Deliveries\Delivery;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestTransporterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'hash' => $this->hash,
            'type' => $this->type,
            'manifest_code' => $this->code ? $this->code->content : null,
            'origin_partner' => [
                'code' => $this->origin_partner ? $this->origin_partner->code : null,
                'address' => $this->origin_partner ? $this->origin_partner->address : null,
            ],
            'destination_partner' => [
                'code' => $this->partner ? $this->partner->code : null,
                'address' => $this->partner ? $this->partner->address : null,
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'total_weight' => $this->weight_borne_total,
            'status' => $this->status,
            'notes' => $this->getNotes($this->status, $this->type)
        ];
    }

    public function getNotes($status, $type): array
    {
        $title = null;
        $desc = null;

        switch ($type) {
            case Delivery::TYPE_RETURN:
                $title = 'Pengembalian';
                break;
            case Delivery::TYPE_PICKUP:
                $title = 'Penjemputan';
                break;
            case Delivery::TYPE_TRANSIT:
                $title = 'Transit';
                break;
            case Delivery::TYPE_DOORING:
                $title = 'Pengantaran';
                break;
            default:
                # code...
                break;
        }

        switch ($status) {
            case Delivery::STATUS_PENDING:
                $desc = 'Menunggu Penjemputan';
                break;
            case Delivery::STATUS_ACCEPTED:
                $desc = 'Driver ditugaskan';
                break;
            case Delivery::STATUS_CANCELLED:
                $desc = 'Pengantaran dibatalkan';
                break;
            case Delivery::STATUS_WAITING_ASSIGN_PACKAGE:
                $desc = 'Menunggu Barang Masuk ke Manifest';
                break;
            case Delivery::STATUS_WAITING_ASSIGN_PARTNER:
                $desc = 'Menunggu Assign Mitra';
                break;
            case Delivery::STATUS_WAITING_PARTNER_ASSIGN_TRANSPORTER:
                $desc = 'Menunggu Mitra Transporter Menugaskan Transporter';
                break;
            case Delivery::STATUS_WAITING_ASSIGN_TRANSPORTER:
                $desc = 'Menunggu Mitra Menugaskan Transporter';
                break;
            case Delivery::STATUS_WAITING_TRANSPORTER:
                $desc = 'Menunggu Transporter';
                break;
            case Delivery::STATUS_LOADING:
                $desc = 'Sedang Melakukan Loading Barang';
                break;
            case Delivery::STATUS_EN_ROUTE:
                $desc = 'Sedang Dalam Perjalanan';
                break;
            case Delivery::STATUS_FINISHED:
                $desc = 'Sampai di Tujuan';
                break;
            default:
                # code...
                break;
        }

        $result = [
            'title' => $title,
            'desc' => $desc
        ];

        return $result;
    }
}
