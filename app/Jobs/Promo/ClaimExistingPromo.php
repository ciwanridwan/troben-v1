<?php

namespace App\Jobs\Promo;

use App\Events\Promo\PromotionClaimed;
use App\Models\Packages\Package;
use App\Models\Promos\ClaimedPromotion;
use App\Models\Promos\Promotion;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ClaimExistingPromo
{
    use Dispatchable;

    /**
     * @var ClaimedPromotion
     */
    public ClaimedPromotion $claimedPromotion;

    public Promotion $promotion;
    public Package $package;

    /**
     * Promotion attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * ClaimExistingPromo constructor.
     * @param Promotion $promotion
     * @param Package $package
     */
    public function __construct(Promotion $promotion, Package $package)
    {
        $this->promotion = $promotion;
        $this->package = $package;
        $this->claimedPromotion = new ClaimedPromotion();
    }

    /**
     * @return bool
     */
    public function handle() : bool
    {
        $this->claimedPromotion->customer_id = $this->package->customer_id;
        $this->claimedPromotion->package_id = $this->package->id;
        $this->claimedPromotion->promotion_id = $this->promotion->id;
        $this->claimedPromotion->claimed_at = Carbon::now();
        $this->claimedPromotion->save();

        event(new PromotionClaimed($this->package));

        return $this->claimedPromotion->exists;
    }
}
