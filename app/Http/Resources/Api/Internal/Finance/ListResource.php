<?php

namespace App\Http\Resources\Api\Internal\Finance;

use App\Models\Partners\Partner;
use App\Models\Payments\Withdrawal;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ListResource extends JsonResource
{
    public function toArray($request)
    {
        $partner = Partner::find($this->partner_id)->only('id', 'name', 'code', 'balance');

        /** @var \App\Models\Payments\Withdrawal */
        $attachment = $this['attachment_transfer'] ?
            Storage::disk('s3')->temporaryUrl('attachment_transfer/'.$this['attachment_transfer'], Carbon::now()->addMinutes(60)) :
            null;
        $data = [
            'id' => $this['id'],
            'hash' => $this['hash'],
            'partner_id' => $partner,
            'first_balance' => $this['first_balance'],
            'created_at' => $this['created_at']->format('Y-m-d'),
            'status' => $this['status'],
            'attachment_transfer' => $attachment,
        ];
        if ($data['status'] == Withdrawal::STATUS_REQUESTED) {
            $data['amount'] = 0;
        } else {
            $data['amount'] = $this['amount'];
        }

        return $data;
    }
}
