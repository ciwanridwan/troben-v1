<?php

namespace App\Jobs\Users;

use App\Events\Users\UserModificationFailed;
use App\Events\Users\UserModified;
use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;

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
        $this->attributes = Validator::make($inputs,[
            'name' => ['filled'],
            'username' => ['filled',"unique:users,username,$user->id,id,deleted_at,NULL"],
            'email' => ['filled',"unique:users,username,$user->id,id,deleted_at,NULL"],
            'phone' => ['filled','unique:users,phone','numeric','phone:AUTO_ID'],
            'password' => ['filled'],
            'email_verified_at' => ['nullable'],
            'remember_token' => ['filled'],
            'verified_at' => ['nullable']
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
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
