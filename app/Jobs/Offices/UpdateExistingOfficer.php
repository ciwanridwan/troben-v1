<?php

namespace App\Jobs\Offices;

use App\Events\Offices\OfficerModificationFailed;
use App\Events\Offices\OfficerModified;
use App\Models\Offices\Office;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateExistingOfficer
{
    use Dispatchable, SerializesModels, InteractsWithQueue, Batchable;

    /**
     * Office instance.
     *
     * @var Office
     */
    public Office $office;

    /**
     * Filtered attributes.
     *
     * @var array
     */
    public array $attributes;

    /**
     * UpdateExistingCustomer constructor.
     *
     * @param Office $office
     * @param $request
     * @throws ValidationException
     */
    public function __construct(Office $office, $request)
    {
        if (array_key_exists('phone', $request)) {
            $output = preg_replace('/^0/', '+62', $request['phone']);
            $replacements = ['phone' => $output];
            $request = array_replace($request, $replacements);
        }

        $this->attributes = Validator::make($request, [
            'username' => ['filled', 'unique'],
            'name' => ['filled'],
            'email' => ['filled', 'email', 'unique:customers,email,'.$office->id.',id,deleted_at,NULL'],
            'phone' => ['filled', 'numeric', 'phone:AUTO,ID', 'unique:customers,phone,'.$office->id.',id,deleted_at,NULL'],
            'address' => ['filled'],
            'password' => ['filled', 'min:8'],
        ])->validate();

        $this->office = $office;
    }

    /**
     * Handle the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        collect($this->attributes)->each(fn ($v, $k) => $this->office->{$k} = $v);
        if ($this->office->isDirty()) {
            if ($this->office->save()) {
                event(new OfficerModified($this->office));
            } else {
                event(new OfficerModificationFailed($this->office));
            }
        }

        return $this->office->exists;
    }
}
