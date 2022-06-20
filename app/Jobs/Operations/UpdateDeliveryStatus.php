<?php

namespace App\Jobs\Operations;

use App\Models\Deliveries\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateDeliveryStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

     /**
     * The podcast instance.
     *
     * @var \App\Models\Deliveries\Delivery
     */
    public $delivery;

    private array $attributes;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Delivery $delivery, array $inputs)
    {
        $this->delivery = $delivery;

        $this->attributes = Validator::make(
            $inputs,
            [
                'status' => ['exists:deliveries,status'],
            ]
        )->validate();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->attributes['status'] = Delivery::STATUS_FINISHED;
        $this->delivery->fill($this->attributes);
        $this->delivery->save();

        return $this->delivery->exists;
    }
}
