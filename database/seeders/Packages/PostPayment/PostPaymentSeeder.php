<?php

namespace Database\Seeders\Packages\PostPayment;

use App\Events\Packages\PackageApprovedByCustomer;
use App\Events\Packages\PackageCheckedByCashier;
use App\Models\Attachment;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Customers\Customer;
use Illuminate\Support\Collection;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Transporter;
use Illuminate\Support\Facades\Event;
use App\Events\Packages\PackageCreated;
use App\Events\Packages\PackagePaymentVerified;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Partners\Pivot\UserablePivot;
use Database\Seeders\AttachmentsTableSeeder;
use Database\Seeders\TransportersTableSeeder;
use Database\Seeders\Packages\PackagesTableSeeder;

class PostPaymentSeeder extends PackagesTableSeeder
{
    public function run(): void
    {
        $packages = new Collection();

        Event::listen(PackageCreated::class, fn (PackageCreated $event) => $packages->push($event->package));

        parent::run();
        $packages->each(function ($package) {
            event(new PackageCheckedByCashier($package));
            event(new PackageApprovedByCustomer($package));
            event(new PackagePaymentVerified($package));
        });

        $partnerQuery = Partner::query()->where('type', Partner::TYPE_BUSINESS);

        $dummyReceipt = Attachment::query()->first();

        $packages->filter(function (Package $package) use ($partnerQuery, $dummyReceipt) {
            return $partnerQuery->whereHas('transporters', fn (Builder $builder) => $builder
                ->where('type', $package->transporter_type))
                ->exists();
        })->each(function (Package $package) use ($partnerQuery, $dummyReceipt) {
            /** @var Partner $partner */
            $partner = $partnerQuery->whereHas('transporters', fn (Builder $builder) => $builder
                ->where('type', $package->transporter_type))
                ->first();

            /** @var Transporter $transporter */
            $transporter = $partner->transporters()->where('type', $package->transporter_type)->first();

            /** @var \App\Models\User $userWarehouse */
            $userWarehouse = $partner->users()->wherePivot('role', UserablePivot::ROLE_WAREHOUSE)->first();

            $package->deliveries()->create([
                'type' => Delivery::TYPE_PICKUP,
                'partner_id' => $partner->id,
                'userable_id' => $transporter->drivers->first()->pivot->id,
                'status' => Delivery::STATUS_FINISHED,
            ]);

            $package->update([
                'estimator_id' => $userWarehouse->id,
            ]);

            $package->attachments()->attach($dummyReceipt);
        });
    }

    // protected function stateResolver(Customer $customer): array
    // {
    //     return array_merge(parent::stateResolver($customer), [
    //         'status' => Package::STATUS_WAITING_FOR_PACKING,
    //         'payment_status' => Package::PAYMENT_STATUS_PAID,
    //     ]);
    // }

    protected function checkOrSeedDependenciesData(): void
    {
        parent::checkOrSeedDependenciesData();

        if (Attachment::query()->where('type', Package::ATTACHMENT_RECEIPT)->count() === 0) {
            $this->call(AttachmentsTableSeeder::class);
        }

        if (Transporter::query()->count() === 0) {
            $this->call(TransportersTableSeeder::class);
        }
    }
}
