<?php

namespace App\Jobs\Users;

use App\Events\Users\UserRoleModified;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateExistingUserRole
{
    use Dispatchable;

    /**
     * @var array
     */
    protected array $attributes;

    public UserablePivot $userable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(UserablePivot $userable, $inputs = [])
    {
        $this->userable = $userable;
        $this->attributes = Validator::make($inputs, [
            'role' => ['required', Rule::in(UserablePivot::ROLES)]
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        collect($this->attributes)->each(fn ($v, $k) => $this->userable->{$k} = $v);
        if ($this->userable->isDirty()) {
            if ($this->userable->save()) {
                event(new UserRoleModified($this->userable));
            }
        }
        return $this->userable->exists;
    }
}
