<?php

namespace Tests\Jobs\Partners\Transporters;

use Tests\TestCase;
use Illuminate\Support\Arr;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Events\Partners\Transporter\TransporterDeleted;
use App\Jobs\Partners\Transporter\DeleteExistingTransporter;

class TransporterDeletionTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    public function test_on_soft_delete()
    {
        Event::fake();

        Partner::factory(1)
            ->has(Transporter::factory()->count(1))
            ->create();

        /** @var \App\Models\Partners\Transporter $transporter */
        $transporter = Transporter::query()->first();

        $response = $this->dispatch(new DeleteExistingTransporter($transporter));
        $this->assertTrue($response);
        $this->assertSoftDeleted($transporter);

        Event::assertDispatched(TransporterDeleted::class);
    }

    public function test_on_force_delete()
    {
        Event::fake();

        Partner::factory(1)
            ->has(Transporter::factory()->count(1))
            ->create();

        /** @var \App\Models\Partners\Transporter $transporter */
        $transporter = Transporter::query()->first();
        $response = $this->dispatch(new DeleteExistingTransporter($transporter, true));
        $this->assertTrue($response);
        $this->assertDatabaseMissing('transporters', Arr::only($transporter->toArray(), 'registration_number'));

        Event::assertDispatched(TransporterDeleted::class);
    }
}
