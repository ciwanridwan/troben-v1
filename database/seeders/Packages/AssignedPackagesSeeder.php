<?php

namespace Database\Seeders\Packages;

use App\Jobs\Deliveries\Actions\AssignTransporterToDelivery;
use Illuminate\Database\Seeder;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\Packages\Actions\AssignFirstPartnerToPackage;

class AssignedPackagesSeeder extends Seeder
{
    /** @noinspection PhpParamsInspection */
    public function run()
    {
        if (Package::query()->count() === 0) {
            $this->call(PackagesTableSeeder::class);
        }

        $partners = Partner::query()->where('type', Partner::TYPE_BUSINESS)->get();

        self::setModelGuarded(
            fn () => Package::query()->take((int) ceil(Package::query()->count() / 2))->get()
                // assign to partner
                ->tap(fn() => $this->command->getOutput()->info('Begin assign partner'))
                ->map(fn (Package $package) => new AssignFirstPartnerToPackage($package, $partners->random()->first()))
                ->each(fn(AssignFirstPartnerToPackage $job) => dispatch_now($job))
                ->each(fn(AssignFirstPartnerToPackage $job) => $this->command->warn('=> package from ' . $job->package->sender_name. ' assigned to partner '. $job->partner->name))
                ->take(ceil(Package::query()->count() / 4))
                // assign to transporter
                ->tap(fn() => $this->command->getOutput()->info('Begin assign driver'))
                ->map(fn(AssignFirstPartnerToPackage $job) => new AssignTransporterToDelivery($job->package->deliveries()->first(), $job->partner->transporters()->first()))
                ->each(fn(AssignTransporterToDelivery $job) => dispatch_now($job))
                ->each(fn(AssignTransporterToDelivery $job) => $this->command
                    ->warn('=> package from ' . $job->delivery->packages()->first()->sender_name. ' assigned to transporter '. $job->transporter->registration_number)));
    }

    private static function setModelGuarded(callable $callback): void
    {
        Model::reguard();
        $callback();
        Model::unguard();
    }
}
