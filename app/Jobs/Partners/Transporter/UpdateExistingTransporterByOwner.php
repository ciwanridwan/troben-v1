<?php

namespace App\Jobs\Partners\Transporter;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Bus\Batchable;
use Illuminate\Validation\Rule;
use App\Models\Partners\Transporter;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\Partners\Transporter\TransporterModified;
use App\Events\Partners\Transporter\TransporterModificationFailed;

class UpdateExistingTransporterByOwner
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
            'type' => ['filled', Rule::in(Transporter::getAvailableTypes())], // jenis kendaraan
            'registration_number' => ['filled'], // nomor stnk atau registrasi
            'vehicle_number' => ['filled'], // nomor rangka
            'is_active' => ['filled', 'boolean'], // active or not
            'photo' => ['array', 'nullable'], // foto kendaraan
            'vehicler_reg' => ['filled'], // stnk

            // not use to update, nullable
            'production_year' => ['filled'],
            'registration_name' => ['filled'],
            'registration_year' => ['filled'],
            'is_verified' => ['nullable'],
            'verified_at' => ['filled'],
        ])->validate();
    }

    /**
     * Updating Existing Transporter Jobs.
     *
     * @return bool
     */
    public function handle(): bool
    {
        // dd($this->attributes);
        $this->transporter->type = $this->attributes['type'] ?? $this->transporter->type;
        $this->transporter->registration_number = $this->attributes['registration_number'] ?? $this->transporter->registration_number;
        $this->transporter->chassis_number = $this->attributes['vehicle_number'] ?? $this->transporter->chassis_number;
        if ($this->attributes['is_active'] === '1') {
            $this->transporter->verified_at = Carbon::now();
        }

        $this->transporter->is_verified = $this->attributes['is_active'] ?? $this->transporter->is_verified;

        if (!empty($this->attributes['vehicler_reg'])) {
            $vehicleImage = handleUpload($this->attributes['vehicler_reg'], 'vehicle_identification');
            $this->transporter->vehicle_identification = $vehicleImage;
        }

        if (!empty($this->attributes['photo'])) {
            foreach ($this->attributes['photo'] as $photo) {
                $image = handleUpload($photo, 'vehicle');
                $this->transporter->images()->create([
                    'path' => $image
                ]);
            }
        }

        $this->transporter->save();
        return $this->transporter->exists;
    }
}
