<?php

namespace App\Jobs\Services;

use App\Models\Service;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use App\Events\Services\NewServiceCreated;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewService
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
    protected array $attributes;

    /**
     * CreateNewService constructor.
     * 
     * @param array $inputs
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct($inputs = [])
    {
        $this->service = new Service();
        $this->attributes = Validator::make($inputs, [
            'code' => ['required','unique:services,code','string','max:3'],
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string','max:255'],
        ])->validate();
    }

    /**
     * Handle job creating service.
     *
     * @return void
     */
    public function handle(): bool
    {
        $this->service->fill($this->attributes);

        if ($this->service->save()) {
            event(new NewServiceCreated($this->service));
        }

        return $this->service->exists;
    }
}
