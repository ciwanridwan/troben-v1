<?php

namespace App\Jobs\Partners;

use App\Models\Partners\Partner;
use App\Models\Payments\Withdrawal;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;

class CreateNewBalanceDisbursement
{
    use Dispatchable;

    /**
     * @var Withdrawal
     */
    public Withdrawal $withdrawal;
    public PartnerRepository $repository;

    /**
     * @var Partner
     */
    public Partner $partner;

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
    public function __construct(Partner $partner, $inputs = [])
    {
        $this->attributes = Validator::make($inputs, [
            'status' => ['required', 'max:255'],
            'amount' => ['nullable', 'max:255'],
            'expired_at' => ['required'],
            'transaction_code' => ['nullable']
        ])->validate();
        $this->withdrawal = new Withdrawal();
        $this->partner = $partner;
        $this->input = $inputs;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): bool
    {
        $this->withdrawal->fill($this->attributes);
        $this->withdrawal->partner_id = $this->partner->id;
        $this->withdrawal->first_balance = $this->partner->balance;
        $this->withdrawal->amount = $this->partner->balance;
        $this->withdrawal->bank_id = $this->input['user']->bankOwner->banks->id;
        $this->withdrawal->account_name = $this->input['user']->bankOwner->account_name;
        $this->withdrawal->account_number = $this->input['user']->bankOwner->account_number;
        $this->withdrawal->transaction_code  = Withdrawal::generateCodeTransaction();
        $this->withdrawal->save();

        if ($this->withdrawal->save()) {
            $this->partner->balance = 0;
            $this->partner->save();
        }

        return $this->withdrawal->exists;
    }
}
