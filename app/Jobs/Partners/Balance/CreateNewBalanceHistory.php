<?php

namespace App\Jobs\Partners\Balance;

use App\Events\Partners\Balance\NewHistoryCreated;
use App\Models\Partners\Balance\History;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateNewBalanceHistory
{
    use Dispatchable;

    /**
     * Partner balance history attributes.
     *
     * @var array $attributes
     */
    public array $attributes;

    /**
     * Partner balance history instance.
     *
     * @var History $history
     */
    public History $history;

    /**
     * Construct of create new partner balance history.
     *
     * @param array $inputs
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $inputs)
    {
        $this->attributes = Validator::make($inputs,[
            'partner_id' => ['required', 'exists:partners,id'],
            'package_id' => ['required', 'exists:packages,id'],
            'balance' => ['required','numeric'],
            'type' => ['required', Rule::in(History::getAvailableType())],
            'description' => ['required', Rule::in(History::getAvailableDescription())],
        ])->validate();
        $this->history = new History();
    }

    /**
     * Create new partner's balance history
     *
     * @return bool
     */
    public function handle(): bool
    {
        $this->history->fill($this->attributes);

        if ($this->history->save()) {
            event(new NewHistoryCreated($this->history));
        }

        return $this->history->exists;
    }
}
