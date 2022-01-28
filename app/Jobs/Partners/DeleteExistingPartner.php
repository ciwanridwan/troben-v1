<?php

namespace App\Jobs\Partners;

use Illuminate\Bus\Batchable;
use App\Models\Partners\Partner;
use Illuminate\Queue\SerializesModels;
use App\Events\Partners\PartnerDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DeleteExistingPartner
{
    use Dispatchable, InteractsWithQueue, Batchable, SerializesModels;

    /**
     * Partner instance.
     *
     * @var \App\Models\Partners\Partner
     */
    public Partner $partner;

    /**
     * Soft delete flag.
     *
     * @var bool
     */
    public bool $softDelete;

    /**
     * DeleteExistingPartner constructor.
     *
     * @param \App\Models\Partners\Partner $partner
     * @param bool                         $force
     */
    public function __construct(Partner $partner, bool $force = false)
    {
        $this->partner = $partner;
        $this->softDelete = ! $force;
    }

    /**
     * Execute the job.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function handle(): bool
    {
        (bool) $result = $this->softDelete ? $this->partner->delete() : $this->partner->forceDelete();

        event(new PartnerDeleted($this->partner));

        return $result;
    }
}
