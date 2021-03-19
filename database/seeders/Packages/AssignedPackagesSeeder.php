<?php

namespace Database\Seeders\Packages;

use App\Jobs\Packages\Partners\AssignFirstPartnerToPackage;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class AssignedPackagesSeeder extends Seeder
{
    public function run()
    {
        $this->call(PackagesTableSeeder::class);

        $partners = Partner::query()->where('type', Partner::TYPE_BUSINESS)->get();

        self::setModelGuarded(
            fn() => Package::query()->take((int) ceil(Package::query()->count() / 2))->get()->each(
                fn(Package $package) => dispatch_now(new AssignFirstPartnerToPackage($package, $partners->random()->first()))));
    }

    private static function setModelGuarded(callable $callback): void
    {
        Model::reguard();
        $callback();
        Model::unguard();
    }
}
