<?php

namespace App\Jobs\Code;

use App\Events\Codes\CodeCreated;
use App\Models\Code;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
use App\Models\Deliveries\Delivery;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewCode
{
    use Dispatchable;

    public Code $code;

    protected $model;

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
                    'content' => Code::generateCodeContent($this->model),
                ];
                $this->code = $this->model->code()->create($this->attributes);
                event(new CodeCreated($this->code));
                return $this->code->exists;

            case $this->model instanceof Item:
                for ($i = 0; $i < $this->model->qty; $i++) {
                    dd('asasa');
                    $this->attributes = [
                        'content' => Code::generateCodeContent($this->model),
                    ];
                    $this->code = $this->model->codes()->create($this->attributes);
                }
                event(new CodeCreated($this->code));
                return $this->code->exists;
        }
    }
}
