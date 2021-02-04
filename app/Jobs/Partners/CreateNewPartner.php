<?php

namespace App\Jobs\Partners;

use App\Events\Partners\NewPartnerCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Validation\Rule;
use App\Models\Partners\Partner;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewPartner implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * filtered attributes.
     * 
     * @var array
     */
    protected array $attributes;

    /**
     * CreateNewPartner constructor.
     *
     * @param array $inputs
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct($inputs = [])
    {
        $this->attributes = Validator::make($inputs,[
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $partner = (new Partner())->fill($this->attributes);

        if ($partner->save()) {
            event(new NewPartnerCreated($partner));
        }

        return $partner->exists;
    }
}
