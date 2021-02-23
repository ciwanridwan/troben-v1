<?php

namespace App\Jobs\Handlings;

use App\Events\Handlings\NewHandlingCreated;
use App\Models\Handling;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;

class CreateNewHandling
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    /**
     * Handling instance.
     *
     * @var \App\Models\Handling
     */
    public Handling $handling;

    /**
     * Filtered attributes.
     *
     * @var array
     */
    public array $attributes;

    /**
     * CreateNewHandling constructor
     *
     * @param array $inputs
     */
    public function __construct($inputs = [])
    {
        $this->handling = new Handling();
        $this->attributes = Validator::make($inputs,[
            'name' => ['required','string','max:255'],
            'price' => ['required','numeric'],
            'type' => ['required','string'],
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $this->handling->fill($this->attributes);

        if ($this->handling->save()) {
            event(new NewHandlingCreated($this->handling));
        }

        return $this->handling->exists;
    }
}
