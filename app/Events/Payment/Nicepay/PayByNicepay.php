<?php

namespace App\Events\Payment\Nicepay;

use App\Broadcasting\Customer\PrivateChannel;
use App\Models\Code;
use App\Models\Customers\Customer;
use App\Models\Packages\Package;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class PayByNicepay
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var Request $params */
    public Request $params;

    /** @var Customer|null $customer */
    public Customer $customer;

    /** @var Package $package */
    public Package $package;

    /**
     * PayByNicepay constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->params = $request;

        /** @var Package $package */
        $this->package = (Code::query()->where('content', $this->params->referenceNo)->first())->codeable;

        $this->customer = $this->package->customer;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel($this->customer,[
            'title' => 'Order '.$this->package->code->content,
            'body' => 'Pembayaran anda sudah diverifikasi',
        ]);
    }
}
