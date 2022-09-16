<?php

namespace App\Jobs\Code;

use App\Models\Code;
use App\Models\Packages\Item;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateExistingCode
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
            case $this->model instanceof Item:
                $codes = $this->model->codes;

                if ($this->model->qty !== $codes->count()) {
                    Code::destroy($codes->pluck('id'));
                    for ($i = 0; $i < $this->model->qty; $i++) {
                        $this->attributes = [
                            'content' => Code::generateCodeContent($this->model),
                        ];
                        $this->code = $this->model->codes()->create($this->attributes);
                    }
                }
        }
    }
}
