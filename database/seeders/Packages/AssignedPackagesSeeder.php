<?php

namespace Database\Seeders\Packages;

use Illuminate\Database\Seeder;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Database\Seeders\TransportersTableSeeder;
use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;
use App\Jobs\Packages\Actions\AssignFirstPartnerToPackage;

class AssignedPackagesSeeder extends Seeder
{
    public function run()
    {
        $this->prepareDependency();

        $partnerQuery = Partner::query()->where('type', Partner::TYPE_BUSINESS);

        self::setModelGuarded(
            fn () => Package::query()->take((int) ceil(Package::query()->count() / 2))->get()
                // assign to partner
                ->tap(fn () => $this->command->getOutput()->info('Begin assigning partner'))
                ->tap(fn () => $this->command->info('=> filtering only packages that has transporter type that available in partners'))
                ->filter(function (Package $package) use ($partnerQuery) {
                    return $partnerQuery->whereHas('transporters', fn (Builder $builder) => $builder
                        ->where('type', $package->transporter_type))
                        ->exists();
                })
                ->map(function (Package $package) use ($partnerQuery) {
                    $partner = $partnerQuery->whereHas('transporters', fn (Builder $builder) => $builder
                        ->where('type', $package->transporter_type))
                        ->first();

                    return new AssignFirstPartnerToPackage($package, $partner);
                })
                ->each(fn (AssignFirstPartnerToPackage $job) => dispatch_now($job))
                ->each(fn (AssignFirstPartnerToPackage $job) => $this->command->warn('=> package from '.$job->package->sender_name.' assigned to partner '.$job->partner->name))
                ->take(ceil(Package::query()->count() / 4))
                // assign to transporter
                ->tap(fn () => $this->command->getOutput()->info('Begin assigning driver'))
                ->map(function (AssignFirstPartnerToPackage $job, $index) {
                    $delivery = $job->package->deliveries()->first();
                    $matchedTransporters = $job->partner->transporters()->where('type', $job->package->transporter_type)->get();

                    /** @var Transporter $transporter */
                    $transporter = $matchedTransporters->get($index % $matchedTransporters->count());

                    return new AssignDriverToDelivery($delivery, $transporter->drivers->first()->pivot);
                })
                ->each(fn (AssignDriverToDelivery $job) => dispatch_now($job))
                ->each(fn (AssignDriverToDelivery $job) => $this->command
                    ->warn('=> package from '.$job->delivery->packages()->first()->sender_name.' assigned to transporter ('.$job->transporter->registration_number.') and driver ('.$job->driver->username.')')));
    }

    private static function setModelGuarded(callable $callback): void
    {
        Model::reguard();
        $callback();
        Model::unguard();
    }

    private function prepareDependency()
    {
        if (Package::query()->count() === 0) {
            $this->call(PackagesTableSeeder::class);
        }

        if (Transporter::query()->count() === 0) {
            $this->call(TransportersTableSeeder::class);
        }
    }
}
