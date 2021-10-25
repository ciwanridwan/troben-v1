<?php

namespace App\Jobs\Partners;

use App\Events\Partners\NewBalanceDisbursementCreated;
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
            'status' => ['nullable', 'max:255'],
            'amount' => ['required', 'max:255'],
            'bank_id' => ['required','max:255'],
            'account_name' => ['required','max:255'],
            'account_number' => ['required', 'max:255'],
        ])->validate();
        $this->withdrawal = new Withdrawal();
        $this->partner = $partner;
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
        $this->withdrawal->save();

        if ($this->withdrawal->save()) {
            event(new NewBalanceDisbursementCreated($this->withdrawal));
        }

        return $this->withdrawal->exists;
    }
}
