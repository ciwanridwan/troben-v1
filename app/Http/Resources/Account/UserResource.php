<?php

namespace App\Http\Resources\Account;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $partnerId = null;

        /** @var \App\Models\User|\App\Models\Customers\Customer $this */
        $data = [
            'hash' => (string) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'username' => $this->username,
            'address' => $this->address,
            'referral_code' => $this->referral_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_active' => $this->is_active,
            'is_ho' => $this->is_admin,
        ];

        if ($this->resource instanceof User) {
            /** @var \Illuminate\Database\Eloquent\Collection $partners */
            $partners = $this->resource->partners;

            $data['partner'] = null;
            if ($partners->count() > 0) {
                $data['partner'] = $partners->first()->only(['name', 'code', 'type', 'address',  'latitude',  'longitude']);
                $data['partner']['as'] = $partners
                    ->where('code', Arr::get($data, 'partner.code'))
                    ->pluck('pivot')->map->role->toArray();
                $partnerId = $partners->first()->id;
            }
            $data['bankOwner'] = null;
            if ($this->resource->bankOwner) {
                $data['bankOwner'] = $this->resource->bankOwner;
                $data['bankOwner']['bank'] = $this->resource->BankOwner->banks;
            }

            $transporters = $this->resource->transporters;

            $data['vehicle'] = null;
            if ($transporters->count() > 0) {
                $data['vehicle'] = $transporters->first()->only(['type', 'registration_name', 'registration_number', 'registration_year']);
            }
        }

        $q = 'SELECT role_id
        FROM role_users_v2
        WHERE user_id = %d';
        $q = sprintf($q, $this->id);
        $roles = collect(DB::select($q))->pluck('role_id');

        $acceptAsHO = [
            'admin-super',
            'ho-cs',
            'ho-warehouse',
            'ho-finance',
            'ho-operation',
            'admin-trawlpack',
            'admin-trawltruck',
            'admin-trawlcarrier',
            'admin-salesagent',
        ];
        foreach ($roles as $r) {
            if (in_array($r, $acceptAsHO)) {
                $data['is_ho'] = true;
            }
        }

        $data['roles'] = $roles;
        $data['partner_id'] = $partnerId;

        $avatar = null;

        if (! is_null($this->avatar)) {
            $avatar = Storage::disk('s3')->temporaryUrl($this->avatar, Carbon::now()->addHours(24));
        }

        $data['avatar'] = $avatar;

        return $data;
    }
}
