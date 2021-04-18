<?php

namespace App\Jobs\Partners\Transporter;

use App\Models\User;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use Illuminate\Validation\Validator;
use App\Models\Partners\Pivot\UserablePivot;
use Veelasky\LaravelHashId\Rules\ExistsByHash;
use Illuminate\Support\Facades\Validator as FacadeValidator;

class AttachDriverToTransporter
{
    public array $attributes;

    public User $actor;

    public User $driver;

    /**
     * AttachUserToTransporter constructor.
     * @param array $inputs
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $inputs)
    {
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->actor = auth()->user();

        /** @var Partner $partner */
        $partner = $this->actor->partners()->first();

        $this->attributes = FacadeValidator::make($inputs, [
            'transporter_hashes' => ['required', 'array'],
            'transporter_hashes.*' => ['string', new ExistsByHash(Transporter::class)],
            'user_hash' => ['required', new ExistsByHash(User::class)],
        ])
            ->after(function (Validator $validator) use ($partner, $inputs) {
                $this->driver = User::byHashOrFail($inputs['user_hash']);

                collect($inputs['transporter_hashes'])->each(
                    fn (string $transporterHash, int $index) => $validator->errors()->addIf(
                        $this->driver
                            ->transporters()
                            ->where('transporters.id', Transporter::hashToId($transporterHash))
                            ->exists(),
                        'transporter_hashes.'.$index,
                        __('transporter and user has already fused!'),
                    )
                );

                // check intersection actor and user_hash input
                $validator->errors()->addIf(
                    $partner->users()
                        ->wherePivot('role', UserablePivot::ROLE_DRIVER)
                        ->where('users.id', User::hashToId($inputs['user_hash']))
                        ->doesntExist(),
                    'user_hash',
                    __('user is not driver or not in the same partners!'),
                );
            })
            ->validate();
    }

    public function handle(): void
    {
        collect($this->attributes['transporter_hashes'])
            ->each(fn (string $transporterHash) => $this
                ->driver
                ->transporters()
                ->attach(Transporter::hashToId($transporterHash), [
                    'role' => UserablePivot::ROLE_DRIVER,
                ]));
    }
}
