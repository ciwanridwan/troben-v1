<?php

namespace App\Jobs;

use App\Models\Code;
use App\Models\CodeLogable;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewCodeLog
{
    use Dispatchable;
    public Code $code;
    public CodeLogable $code_logable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Code $code)
    {
        $this->code = $code;
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
