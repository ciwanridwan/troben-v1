<?php

namespace App\Jobs\Users;

use App\Models\User;
use Illuminate\Bus\Batchable;
use App\Events\Users\NewUserCreated;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;

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
        $this->attributes = Validator::make($inputs, [
            'name' => ['required'],
            'username' => ['required', 'unique:users,username', 'regex:/^\S*$/u'],
            'email' => ['required', 'unique:users,email', 'email'],
            'phone' => ['required', 'unique:users,phone', 'numeric', 'phone:AUTO,ID'],
            'password' => ['required','confirmed'],
            'email_verified_at' => ['nullable'],
            'remember_token' => ['filled'],
            'verified_at' => ['nullable'],
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
