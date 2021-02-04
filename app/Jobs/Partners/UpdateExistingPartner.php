<?php

namespace App\Jobs\Partners;

use Illuminate\Bus\Batchable;
use Illuminate\Validation\Rule;
use App\Models\Partners\Partner;
use Illuminate\Queue\SerializesModels;
use App\Events\Partners\PartnerModified;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\Partners\PartnerModificationFailed;

class UpdateExistingPartner implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    /**
     * Partner instance.
     * 
     * @var App\Models\Partners\Partner
     */
    public Partner $partner;

    /**
     * Filtered Attributes.
     * 
     * @var array
     */
    protected array $attributes;

    /**
     * @param App\Models\Partners\Partner $partner
     * @param array                       $inputs
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(Partner $partner, $inputs = [])
    {
        $this->partner = $partner;
        $this->attributes = Validator::make($inputs, [
            'name' => ['required','string','max:255'],
            'code' => ['required','string','max:255'],
            'contact_email' => ['nullable','email'],
            'contact_phone' => ['nullable','numeric','phone:AUTO,ID'],
            'address' => ['nullable','string','max:255'],
            'geo_location' => ['nullable'],
            'type' => ['required',Rule::in(Partner::getAvailableTypes())],
        ])->validate();
    }

    /**
     * Handle the job.
     * 
     * @return bool
     */
    public function handle(): bool
    {
        foreach ($this->attributes as $index => $partner) {
            $this->partner->$index = $partner;
        }

        if ($this->partner->isDirty()) {
            if ($this->partner->save()) {
                event(new PartnerModified($this->partner));
            } else {
                event(new PartnerModificationFailed($this->partner));
            }
        }

        return $this->partner->exists;
    }
}
