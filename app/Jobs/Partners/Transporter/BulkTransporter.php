<?php

namespace App\Jobs\Partners\Transporter;

use Illuminate\Bus\Batchable;
use Illuminate\Validation\Rule;
use App\Models\Partners\Partner;
use Illuminate\Support\Collection;
use App\Models\Partners\Transporter;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\Partners\Transporter\TransporterBulked;

class BulkTransporter
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    /**
     * Partner instances.
     *
     * @var \App\Models\Partners\Partner
     */
    public Partner $partner;

    /**
     * Collection of transporter.
     *
     * @var \Illuminate\Support\Collection|\App\Models\Partners\Transporter[] $transporters
     */
    public Collection $transporters;

    /**
     * Filtered attributes.
     *
     * @var array $attributes
     */
    public array $attributes;

    /**
     * Flag jobs done.
     *
     * @var bool $finish
     */
    public bool $finish;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Partners\Partner $partner
     * @param array $inputs
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(Partner $partner, $inputs = [])
    {
        $this->partner = $partner;
        $this->attributes = Validator::make($inputs, [
            '*.registration_number' => ['required', 'string', 'max:255'],
            '*.type' => ['required', Rule::in(Transporter::getAvailableTypes())],
            '*.production_year' => ['required'],
            '*.registration_name' => ['required'],
            '*.registration_year' => ['required'],
            '*.is_verified' => ['nullable'],
            '*.verified_at' => ['filled'],
        ])->validate();
    }

    /**
     * @return bool
     */
    public function handle(): bool
    {
        $this->transporters = $this->partner->transporters()->createMany($this->attributes);

        if ($this->transporters) {
            event(new TransporterBulked($this->transporters));
        }

        if (Transporter::whereIn('id', $this->transporters->pluck('id')->all())->get()->count() == $this->transporters->count()) {
            $this->finish = true;
        }

        return $this->finish;
    }
}
