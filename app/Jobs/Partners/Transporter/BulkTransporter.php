<?php

namespace App\Jobs\Partners\Transporter;

use App\Events\Partners\Transporter\TransporterBulked;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
     * @return void
     */
    public function __construct(Partner $partner, $inputs = [])
    {
        $this->partner = $partner;
        $this->attributes = Validator::make($inputs,[
            '*.name' => ['required','string','max:255'],
            '*.registration_number' => ['required','string','max:255'],
            '*.type' => ['required', Rule::in(Transporter::getAvailableTypes())],
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->transporters = $this->partner->transporters()->createMany($this->attributes);

        if ($this->transporters) {
            event(new TransporterBulked($this->transporters));
        }

        if(Transporter::whereIn('id',$this->transporters->pluck('id')->all())->get()->count() == $this->transporters->count()) {
            $this->finish = true;
        }

        return $this->finish;
    }
}
