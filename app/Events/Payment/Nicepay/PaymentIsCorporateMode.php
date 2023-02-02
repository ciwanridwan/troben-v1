<?php

namespace App\Events\Payment\Nicepay;

use App\Broadcasting\Customer\PrivateChannel;
use App\Models\Code;
use App\Models\Customers\Customer;
use App\Models\Notifications\Template;
use App\Models\Packages\Package;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class PaymentIsCorporateMode
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var Package $package */
    public Package $package;

    /**
     * PaymentIsCorporateMode constructor.
     * @param Package $package
     */
    public function __construct(Package $package)
    {
        /** @var Package $package */
        $this->package = $package;
    }
}
