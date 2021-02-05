<?php

namespace App\Jobs\Partners;

use App\Events\Partners\PartnerDeleted;
use Illuminate\Bus\Batchable;
use App\Models\Partners\Partner;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DeleteExistingPartner
{
    use Dispatchable, InteractsWithQueue, Batchable, SerializesModels;

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
            event(new PartnerDeleted($this->partner));
        }

        return $this->customer->deleted_at !== null;
    }
}
