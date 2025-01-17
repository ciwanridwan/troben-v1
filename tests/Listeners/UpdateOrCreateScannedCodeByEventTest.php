<?php

namespace Tests\Listeners;

use App\Jobs\Deliveries\Actions\ProcessFromCodeToDelivery;
use App\Jobs\Deliveries\CreateNewDelivery;
use App\Listeners\Codes\UpdateOrCreateScannedCode;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\User;
use Database\Seeders\Packages\AssignedPackagesSeeder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateOrCreateScannedCodeByEventTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    public bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AssignedPackagesSeeder::class);
    }

    public function test_on_package_items_assigned_by_warehouse()
    {
        $delivery = Delivery::factory()->makeOne();
        $package = Package::with('items')->first();
        $user = User::partnerRole(Partner::TYPE_POOL, UserablePivot::ROLE_WAREHOUSE)->get()->random()->first();
        $this->actingAs($user);

        $this->expectsJobs(CreateNewDelivery::class);
        $job = new CreateNewDelivery($delivery->getAttributes(), $user->partner);
        $this->dispatch($job);
        $delivery = $job->delivery;

        $codes = $package->items->pluck('codes')->flatten(1);
        $code_contents = $codes->pluck('content')->toArray();
        $this->expectsJobs(ProcessFromCodeToDelivery::class);
        $job = new ProcessFromCodeToDelivery($delivery, [
            'code' => $code_contents,
            'role' => UserablePivot::ROLE_WAREHOUSE,
            'status' => Deliverable::STATUS_PREPARED_BY_ORIGIN_WAREHOUSE,
        ]);
        $this->dispatch($job);
    }
    private function runListener(object $event): void
    {
        $listener = new UpdateOrCreateScannedCode();

        $listener->handle($event);
    }
}
