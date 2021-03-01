<?php

namespace App\Jobs\Users;

use Illuminate\Validation\Rule;
use App\Events\Users\UserRoleModified;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Partners\Pivot\UserablePivot;

class UpdateExistingUserRole
{
    use Dispatchable;

    public UserablePivot $userable;

    /**
     * @var array
     */
    protected array $attributes;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(UserablePivot $userable, $inputs = [])
    {
        $this->userable = $userable;
        $this->attributes = Validator::make($inputs, [
            'role' => ['required', Rule::in(UserablePivot::ROLES)],
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
