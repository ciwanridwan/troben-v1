<?php

namespace App\Jobs\Users;

use App\Models\User;
use Illuminate\Bus\Batchable;
use App\Events\Users\UserModified;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\Users\UserModificationFailed;
use Illuminate\Support\Arr;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class UpdateExistingUser
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    /**
     * User instance.
     *
     * @var \App\Models\User
     */
    public User $user;

    /**
     * Filtered Attributes.
     *
     * @var array
     */
    public array $attributes;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $inputs = [])
    {
        $this->user = $user;

        ! Arr::has($inputs, 'phone') ?: $inputs['phone'] =
            PhoneNumberUtil::getInstance()->format(
                PhoneNumberUtil::getInstance()->parse($inputs['phone'], 'ID'),
                PhoneNumberFormat::E164
            );
        $this->attributes = Validator::make($inputs, [
            'name' => ['filled'],
            'referral_code' => ['filled'],
            'username' => ['filled', "unique:users,username,$user->id,id,deleted_at,NULL", 'regex:/^\S*$/u'],
            'email' => ['filled', "unique:users,email,$user->id,id,deleted_at,NULL"],
            'phone' => ['filled', "unique:users,phone,$user->id,id,deleted_at,NULL", 'numeric', 'phone:AUTO,ID'],
            'password' => ['filled', 'confirmed'],
            'email_verified_at' => ['nullable'],
            'remember_token' => ['filled'],
            'fcm_token' => ['nullable','unique:users,fcm_token,'.$user->id.',id,deleted_at,NULL'],
            'verified_at' => ['nullable'],
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        if (array_key_exists('referral_code', $this->attributes)) {
            if (User::where('referral_code', $this->attributes['referral_code'])->first() == null) {
                return $this->referral = 'failed';
            }
        }

        collect($this->attributes)->each(fn ($v, $k) => $this->user->{$k} = $v);

        if ($this->user->isDirty()) {
            if ($this->user->save()) {
                event(new UserModified($this->user));
            } else {
                event(new UserModificationFailed($this->user));
            }
        }

        return $this->user->exists;
    }
}
