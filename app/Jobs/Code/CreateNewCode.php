<?php

namespace App\Jobs\Code;

use App\Concerns\Models\HasCode;
use App\Models\Code;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;

class CreateNewCode
{

    use Dispatchable;

    protected $model;

    protected Code $code;

    protected array $attributes;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        switch (true) {
            case $this->model instanceof Package || $this->model instanceof Delivery:
                $this->attributes = [
                    'content' => Code::generateCodeContent($this->model)
                ];
                $this->code = $this->model->code()->create($this->attributes);
                return $this->code->exists;

            case $this->model instanceof Item:
                for ($i = 0; $i < $this->model->qty; $i++) {
                    $this->attributes = [
                        'content' => Code::generateCodeContent($this->model)
                    ];
                    $this->code = $this->model->codes()->create($this->attributes);
                }
                return $this->code->exists;
        }
    }
}
