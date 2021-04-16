<?php

namespace App\Observers;

use App\Jobs\Code\CreateNewCode;
use Illuminate\Foundation\Bus\DispatchesJobs;

class CodeObserver
{
    use DispatchesJobs;
    protected $model;

    public function creating($model)
    {
        $model->barcode = 'DUMMY';
    }
    public function created($model)
    {
        $job = new CreateNewCode($model);
        $model->dispatch($job);
    }
}
