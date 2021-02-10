<?php

namespace App\Jobs\Services;

use App\Events\Services\ServiceModificationFailed;
use App\Events\Services\ServiceModified;
use App\Models\Service;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;

class UpdateExistingService
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    /**
     * Service instance.
     * 
     * @var \App\Models\Service
     */
    public Service $service;

    /**
     * Filtered attributes.
     * 
     * @var array
     */
    public array $attributes;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Service $service, $inputs = [])
    {
        $this->service = $service;
        $this->attributes = Validator::make($inputs, [
            'code' => ['filled','unique:services,code','string','max:3'],
            'name' => ['filled','string','max:255'],
            'description' => ['nullable','string','max:255'],
        ])->validate();
    }

    /**
     * Update Existing Service Job.
     *
     * @return void
     */
    public function handle(): bool
    {
        collect($this->attributes)->each(fn ($v,$k) => $this->service->{$k} = $v);

        if ($this->service->isDirty() && $this->service->save()) {
            event(new ServiceModified($this->service));
        } else {
            event(new ServiceModificationFailed($this->service));
        }

        return $this->service->exists;
    }
}
