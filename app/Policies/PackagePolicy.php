<?php

namespace App\Policies;

use App\Models\Packages\Package;
use App\Models\Customers\Customer;
use App\Models\User;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Auth\Access\HandlesAuthorization;

class PackagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Customers\Customer|\App\Models\User  $user
     * @param  \App\Models\Packages\Package  $package
     * @return mixed
     */
    public function view($user, Package $package): bool
    {
        if ($user instanceof Customer) {
            return $package->customer_id == $user->id;
        }

        if ($user instanceof User) {
            return $this->getPartnerRepository()->queries()->getPackagesQuery()->where('id', $package->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Customers\Customer|\App\Models\User  $user
     * @param  \App\Models\Packages\Package  $package
     * @return mixed
     */
    public function update($user, Package $package): bool
    {
        if ($user instanceof Customer) {
            return $package->customer_id == $user->id;
        }

        if ($user instanceof User) {
            return $this->getPartnerRepository()->queries()->getPackagesQuery()->where('id', $package->id)->exists();
        }

        return false;
    }

    private function getPartnerRepository(): PartnerRepository
    {
        return app(PartnerRepository::class);
    }
}
