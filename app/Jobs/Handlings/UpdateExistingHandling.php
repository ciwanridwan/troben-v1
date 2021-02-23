<?php

namespace App\Jobs\Handlings;

use App\Events\Handlings\HandlingModificationFailed;
use App\Events\Handlings\HandlingModified;
use App\Models\Handling;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;

class UpdateExistingHandling
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
     * UpdateExistingHandling constructor.
     *
     * @param \App\Models\Handling  $handling
     * @param array                 $inputs
     */
    public function __construct(Handling $handling, $inputs = [])
    {
        $this->handling = $handling;
        $this->attributes = Validator::make($inputs,[
            'name' => ['filled','string','max:255'],
            'price' => ['filled','numeric'],
            'type' => ['filled','string']
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        collect($this->attributes)->each(fn ($v,$k) => $this->handling->{$k} = $v);

        if ($this->handling->isDirty() && $this->handling->save()) {
            event(new HandlingModified($this->handling));
        } else {
            event(new HandlingModificationFailed($this->handling));
        }

        return $this->handling->exists;
    }
}
