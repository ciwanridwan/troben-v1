<?php

namespace App\Jobs\Partners;

use App\Exceptions\Error;
use App\Http\Response;
use App\Jobs\Users\Actions\VerifyExistingUser;
use App\Jobs\Users\CreateNewUser;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Validation\ValidationException;

class CreateNewEmployee
{
    use Dispatchable;

    /**
     * @var array
     */
    protected array $attributes;

    /**
     * @var User
     */
    public User $employee;

    /**
     * @var Partner
     */
    protected Partner $partner;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Partner $partner, array $inputs)
    {

        $this->partner = $partner;
        $this->attributes =  $inputs;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $job = new CreateNewUser($this->attributes);
        dispatch_now($job);

        throw_if(!$job, Error::make(Response::RC_DATABASE_ERROR));

        $verifyJob = new VerifyExistingUser($job->user);
        dispatch_now($verifyJob);

        foreach ($this->attributes['role'] as $role) {
            $pivot = new UserablePivot();
            $pivot->fill([
                'user_id' => $job->user->id,
                'userable_type' => Partner::class,
                'userable_id' => $this->partner->getKey(),
                'role' => $role,
            ])->save();
        }

        $this->employee = $job->user;

        return $this->employee->exists;
    }
}
