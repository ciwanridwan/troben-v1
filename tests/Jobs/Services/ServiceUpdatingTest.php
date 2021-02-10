<?php

namespace Tests\Feature\Services;

use App\Events\Services\ServiceModified;
use App\Jobs\Services\UpdateExistingService;
use App\Models\Service;
use Database\Seeders\ServiceTableSeeder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ServiceUpdatingTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    protected array $updateData;

    public function setUp(): void
    {
        parent::setUp();

        $this->updateData = [
            'name' => $this->faker->name,
            'description' => $this->faker->text(),
        ];
        $this->seed(ServiceTableSeeder::class);
    }

    /**
     * Get Service Test Subject.
     * 
     * @return \App\Models\Service|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     */
    public function getTestSubject($latest = false)
    {
        $service = Service::query();
        return $latest 
            ? $service->latest()->first()
            : $service->first();
    }

    public function test_on_valid_data()
    {
        $subject = $this->getTestSubject();
        Event::fake();

        $job = new UpdateExistingService($subject, $this->updateData);
        $this->assertTrue($this->dispatch($job));

        $this->assertDatabaseHas('services', Arr::only($this->updateData,['name']));
        Event::assertDispatched(ServiceModified::class);
    }

    public function test_on_invalid_data()
    {
        $subject = $this->getTestSubject();

        Event::fake();

        $this->expectException(ValidationException::class);
        $this->dispatch(new UpdateExistingService($subject,[
            'code' => 'aaaaa',    
        ]));

        Event::assertNotDispatched(ServiceModified::class);
    }

    public function test_on_unique_code()
    {
        $subject = $this->getTestSubject();
        $secondSubject = $this->getTestSubject(true);

        Event::fake();

        $this->expectException(ValidationException::class);
        $this->dispatch(new UpdateExistingService($subject,[
            'code' => $secondSubject->code,    
        ]));

        Event::assertNotDispatched(ServiceModified::class);
    }
}
