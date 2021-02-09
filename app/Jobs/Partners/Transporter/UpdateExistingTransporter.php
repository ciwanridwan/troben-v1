<?php

namespace App\Jobs\Partners\Transporter;

use Illuminate\Bus\Batchable;
use Illuminate\Validation\Rule;
use App\Models\Partners\Transporter;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\Partners\Transporter\TransporterModified;
use App\Events\Partners\Transporter\TransporterModificationFailed;

class UpdateExistingTransporter
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    /**
     * Transporter instance.
     * @var Transporter
     */
    public Transporter $transporter;

    /**
     * Attributes.
     * 
     * @var array
     */
    public array $attributes;

    /**
     * UpdateExistingTransporter construct.
     * @param \App\Models\Partners\Transporter $transporter
     * @param array                            $inputs
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(Transporter $transporter, $inputs = [])
    {
        $this->transporter = $transporter;
        $this->attributes = Validator::make($inputs, [
            'name' => ['filled'],
            'registration_number' => ['filled'],
            'type' => ['filled', Rule::in(Transporter::getAvailableTypes())],
        ])->validate();
    }

    /**
     * Updating Existing Transporter Jobs.
     * 
     * @return bool
     */
    public function handle(): bool
    {
        collect($this->attributes)->each(fn ($v, $k) => $this->transporter->{$k} = $v);

        if ($this->transporter->isDirty()) {
            if ($this->transporter->save()) {
                event(new TransporterModified($this->transporter));
            } else {
                event(new TransporterModificationFailed($this->transporter));
            }
        }

        return $this->transporter->exists;
    }
}