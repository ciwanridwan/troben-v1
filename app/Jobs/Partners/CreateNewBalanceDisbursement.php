<?php

namespace App\Jobs\Partners;

use App\Events\Partners\NewBalanceDisbursementCreated;
use App\Models\Partners\Partner;
use App\Models\Payments\Withdrawal;
use App\Models\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;

class CreateNewBalanceDisbursement
{
    use Dispatchable;

    /**
     * @var Withdrawal
     */
    public Withdrawal $withdrawal;

    /**
     * @var User
     */
    public User $user;

    /**
     * filtered attributes.
     *
     * @var array
     */
    protected array $attributes;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $inputs = [])
    {
        $this->attributes = Validator::make($inputs, [
            'amount' => ['required', 'max:255'],
            'bank_id' => ['required','max:255'],
            'account_name' => ['required','max:255'],
            'account_number' => ['required', 'max:255'],
        ])->validate();
        $this->withdrawal = new Withdrawal();
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): bool
    {
        $this->withdrawal->fill($this->attributes);
        $this->withdrawal->partner_id = $this->user->partners[0]->id;
        $this->withdrawal->first_balance = $this->user->partners[0]->balance;
        $this->withdrawal->last_balance = 0;
        $this->withdrawal->status = Withdrawal::STATUS_CREATED;
        $this->withdrawal->save();

        if ($this->withdrawal->save()) {
            event(new NewBalanceDisbursementCreated($this->withdrawal));
        }

        return $this->withdrawal->exists;
    }
}
