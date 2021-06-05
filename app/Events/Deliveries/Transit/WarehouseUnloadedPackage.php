<?php

namespace App\Events\Deliveries\Transit;

use App\Models\Code;
use App\Models\Deliveries\Delivery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WarehouseUnloadedPackage
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Delivery $delivery;

    public Code $code;

    public array $codes;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Delivery $delivery, array $inputs = [])
    {
        $this->delivery = $delivery;
        $deliveryCodes = $this->delivery->item_codes->pluck('content')->toArray();
        $this->codes = Validator::validate($inputs, [
            'code.*' => [Rule::in($deliveryCodes)]
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
