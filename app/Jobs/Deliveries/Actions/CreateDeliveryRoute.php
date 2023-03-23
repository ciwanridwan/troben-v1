<?php

namespace App\Jobs\Deliveries\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CreateDeliveryRoute implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $originRegencyId;

    public int $destinationRegencyId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $originWarehouseId, int $destinationRegencyId)
    {
        $this->originRegencyId = $originWarehouseId;
        $this->destinationRegencyId = $destinationRegencyId;

        $getRoute = DB::table('tranport_routes')->where('');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
