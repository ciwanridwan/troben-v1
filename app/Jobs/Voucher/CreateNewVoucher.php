<?php

namespace App\Jobs\Voucher;

use App\Models\Partners\Voucher;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateNewVoucher
{
    use Dispatchable;

    /**
     * Package instance.
     *
     * @var Voucher
     */
    public Voucher $voucher;

    /**
     * @var Request
     */
    public Request $request;

    public PartnerRepository $repository;
    /**
     * Voucher attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * CreateNewPromotion constructor.
     * @param array $inputs
     * @throws ValidationException
     */
    public function __construct( array $inputs, Request $request, PartnerRepository $repository)
    {
        $this->attributes = Validator::make($inputs, [
            'partner_id' => ['required'],
            'discount' => ['nullable'],
        ])->validate();

        $this->voucher = new Voucher();
        $this->request = $request;
        $this->repository = $repository;
    }

    /**
     * @return bool
     */
    public function handle() : bool
    {
        $this->voucher->fill($this->attributes);
        $this->voucher->user_id = $this->repository->getDataUser()->id;
        $this->voucher->title = 'Voucher Pengiriman';
        $this->voucher->code = Str::random(5);
        $this->voucher->start_date = Date::now();
        $this->voucher->end_date = Date::now()->addMonth();

        $this->voucher->save();

        return $this->voucher->exists;
    }
}
