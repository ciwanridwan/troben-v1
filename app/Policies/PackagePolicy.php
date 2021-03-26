<?php

namespace App\Policies;

use App\Models\Customers\Customer;
use App\Models\Packages\Package;
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
            return $user->packages()->where('id', $package->id)->exists();
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
            return $user->packages()->where('id', $package->id)->exists();
        }

        return false;
    }
}
