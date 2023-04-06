<?php

namespace App\Jobs\Partners\Transporter;

use Illuminate\Validation\Rule;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\Partners\Transporter\TransporterCreated;

class CreateNewTransporter
{
    use Dispatchable;

    /**
     * Partner Instance.
     *
     * @var \App\Models\Partners\Partner
     */
    public Partner $partner;

    /**
     * Partner Instance.
     *
     * @var \App\Models\Partners\Transporter
     */
    public Transporter $transporter;

    /**
     * Partner Instance.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Partners\Partner $partner
     * @param array                        $inputs
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(Partner $partner, $inputs = [])
    {
        $this->partner = $partner;
        $this->attributes = Validator::make($inputs, [
            'type' => ['required', Rule::in(Transporter::getAvailableTypes())],
            'registration_number' => ['required'],
            'production_year' => ['required'],
            'registration_name' => ['filled'],
            'registration_year' => ['filled'],
            'is_verified' => ['nullable'],
            'verified_at' => ['nullable'],
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $this->transporter = $this->partner->transporters()->create($this->attributes);

        if ($this->transporter) {
            event(new TransporterCreated($this->transporter));
        }

        return $this->transporter->exists;
    }
}
