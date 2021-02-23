<?php

namespace Tests\Jobs\Handlings;

use App\Events\Handlings\HandlingDeleted;
use App\Jobs\Handlings\DeleteExistingHandling;
use App\Models\Handling;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class HandlingDeletionTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    public function test_on_force_delete()
    {
        Handling::factory(1)->create();
        Event::fake();

        /** @var \App\Models\Handling $subject */
        $subject = Handling::query()->first();
        $response = $this->dispatch(new DeleteExistingHandling($subject,true));
        $this->assertTrue($response);
        $this->assertDatabaseMissing('products', Arr::only($subject->toArray(), 'name'));

        Event::assertDispatched(HandlingDeleted::class);
    }
}
