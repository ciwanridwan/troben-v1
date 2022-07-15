<?php

namespace App\Jobs\Voucher;

use App\Events\Promo\PromotionClaimed;
use App\Models\Packages\Package;
use App\Models\Partners\ClaimedVoucher;
use App\Models\Partners\Voucher;
use Illuminate\Foundation\Bus\Dispatchable;

class ClaimDiscountVoucher
{
    use Dispatchable;

    /**
     * Package instance.
     *
     * @var Voucher
     */
    public Voucher $voucher;

    /**
     * @var ClaimedVoucher
     */
    public ClaimedVoucher $claimedVoucher;

    /**
     * @var int
     */
    public int $customer_id;

    /**
     * @var int
     */
    public int $package_id;
    /**
     * CreateNewPromotion constructor.
     */
    public function __construct(Voucher $voucher, int $package_id, int $customer_id)
    {
        $this->claimedVoucher = new ClaimedVoucher();
        $this->voucher = $voucher;
        $this->customer_id = $customer_id;
        $this->package_id = $package_id;
    }

    /**
     * @return bool
     */
    public function handle() : bool
    {
        $this->claimedVoucher->voucher_id = $this->voucher->getKey();
        $this->claimedVoucher->user_id = $this->voucher->user_id;
        $this->claimedVoucher->partner_id = $this->voucher->partner_id;
        $this->claimedVoucher->customer_id = $this->customer_id;
        $this->claimedVoucher->package_id = $this->package_id;
        $this->claimedVoucher->discount = $this->voucher->discount;
        $this->claimedVoucher->code = $this->voucher->code;
        $this->claimedVoucher->save();
        $package = Package::find($this->package_id);
        event(new PromotionClaimed($package));

        return $this->claimedVoucher->exists;
    }
}
