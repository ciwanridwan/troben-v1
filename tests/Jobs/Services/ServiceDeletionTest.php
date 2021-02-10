<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Models\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use App\Events\Services\ServiceDeleted;
use Database\Seeders\ServiceTableSeeder;
use App\Jobs\Services\DeleteExistingService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServiceDeletionTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    public function test_on_force_delete()
    {
        Event::fake();

        /** @var \App\Models\Service $subject */
        $subject = $this->getTestSubject();
        $response = $this->dispatch(new DeleteExistingService($subject, true));
        $this->assertTrue($response);
        $this->assertDatabaseMissing('services', Arr::only($subject->toArray(), 'code'));

        Event::assertDispatched(ServiceDeleted::class);
    }

    /**
     * Get Service Test Subject.
     * 
     * @return \App\Models\Service|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     */
    public function getTestSubject()
    {
        $this->seed(ServiceTableSeeder::class);

        return Service::query()->first();
    }
}
