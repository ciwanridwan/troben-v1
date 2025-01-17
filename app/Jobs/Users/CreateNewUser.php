<?php

namespace App\Jobs\Users;

use App\Models\User;
use Illuminate\Bus\Batchable;
use App\Events\Users\NewUserCreated;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class CreateNewUser
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
    public function __construct($inputs = [])
    {
        $this->user = new User();
        ! Arr::has($inputs, 'phone') ?: $inputs['phone'] =
            PhoneNumberUtil::getInstance()->format(
                PhoneNumberUtil::getInstance()->parse($inputs['phone'], 'ID'),
                PhoneNumberFormat::E164
            );
        $this->attributes = Validator::make($inputs, [
            'name' => ['required'],
            'username' => ['required', 'unique:users,username,NULL,id,deleted_at,NULL', 'regex:/^\S*$/u'],
            'email' => ['required', 'unique:users,email,NULL,id,deleted_at,NULL', 'email'],
            'phone' => ['phone:AUTO,ID', 'required', 'unique:users,phone,NULL,id,deleted_at,NULL', 'numeric'],
            'password' => ['required'],
            'email_verified_at' => ['nullable'],
            'remember_token' => ['filled'],
            'verified_at' => ['nullable'],
            'referral_code' => ['nullable'],
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->user->fill($this->attributes);

        if ($this->user->save()) {
            event(new NewUserCreated($this->user));
        }

        return $this->user->exists;
    }
}
