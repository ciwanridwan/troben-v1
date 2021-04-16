<?php

namespace App\Observers;

use App\Jobs\Code\CreateNewCode;
use App\Models\Packages\Package;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;

class CodeObserver
{
    use DispatchesJobs;
    protected $model;

    function creating($model)
    {
        $model->barcode = 'DUMMY';
    }
    function created($model)
    {
        $job = new CreateNewCode($model);
        $model->dispatch($job);
    }
}
