<?php

namespace App\Jobs\Code;

use App\Models\Packages\Item;
use App\Models\Packages\Package;
use Illuminate\Foundation\Bus\Dispatchable;

class createNewCode
{
    use Dispatchable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Package|Item $model)
    {
        //
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
