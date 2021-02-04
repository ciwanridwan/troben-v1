<?php

namespace App\Jobs\Partners;

use App\Models\Partners\Partner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteExistingPartner implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Partner instance.
     * 
     * @var App\Models\Partners\Partner
     */
    public Partner $partner;

    /**
     * DeleteExistingPartner constructor.
     * 
     * @param App\Models\Partners\Partner $partner
     */
    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->partner->delete()) {
            event();
        }

        return $this->customer->deleted_at !== null;
    }
}
