<?php

namespace App\Http\Resources\Account;

use App\Models\Partners\BankAccount;
use App\Models\Payments\Bank;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBankResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
//        dd(Bank::find($this->bank_id));
//        dd(BankAccount::find($this->bank_id));
        $bank = Bank::find($this->bank_id);

        /** @var \App\Models\Partners\BankAccount $this */
        $data = [
            'id' => $this->id,
            'bank' => Bank::find($this->bank_id),
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'created_at' => date('Y-m-d h:i:s', strtotime($this->created_at)),
            'updated_at' => date('Y-m-d h:i:s', strtotime($this->updated_at)),
        ];

        return $data;
    }
}
