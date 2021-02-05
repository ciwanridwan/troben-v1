<?php

namespace App\Jobs\Partners\Transporter;

use Illuminate\Validation\Rule;
use App\Models\Partners\Partner;
use Illuminate\Support\Facades\App;
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
     * @var App\Models\Partners\Partner
     */
    public Partner $partner;

    /**
     * Partner Instance.
     *
     * @var App\Models\Partners\Transporter
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
     * @return void
     */
    public function __construct(Partner $partner, $inputs = [])
    {
        $this->partner = $partner;
        $this->attributes = Validator::make($inputs, [
            'name' => ['required'],
            'registration_number' => ['required'],
            'type' => ['required', Rule::in(Transporter::getAvailableTypes())],
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->transporter = $this->partner->transporters()->create($this->attributes);
        if ($this->transporter) {
            event(new TransporterCreated($this->transporter));
        }

        return $this->transporter->exists;
    }
}
