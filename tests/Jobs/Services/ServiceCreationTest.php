<?php

namespace Tests\Jobs\Services;

use App\Events\Services\NewServiceCreated;
use App\Jobs\Services\CreateNewService;
use App\Models\Service;
use Database\Seeders\ServiceTableSeeder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ServiceCreationTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    private $data;
    private Service $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'code' => substr($this->faker->lexify(),0,2),
            'name' => $this->faker->name,
            'description' => $this->faker->text(),
        ];
    }

    public function test_on_valid_data()
    {
        Event::fake();

        $job = new CreateNewService($this->data);

        $this->assertTrue($this->dispatch($job));

        $this->assertInstanceOf(Service::class, $job->service);

        $this->assertTrue($job->service->exists);

        $this->assertDatabaseHas('services', Arr::only($this->data,['code']));

        Event::assertDispatched(NewServiceCreated::class);
    }

    public function test_on_missing_data()
    {
        $this->expectException(ValidationException::class);
        Event::fake();

        $data = Arr::only($this->data, ['name','description']);

        $job = new CreateNewService($data);
        $this->assertTrue($this->dispatch($job));

        Event::assertNotDispatched(NewServiceCreated::class);
    }

    public function test_on_invalid_data()
    {
        $this->expectException(ValidationException::class);
        Event::fake();

        $data = $this->data;
        $data['code'] = 'abcd';

        $job = new CreateNewService($data);
        $this->assertTrue($this->dispatch($job));

        Event::assertNotDispatched(NewServiceCreated::class);
    }

    public function test_on_unique_code()
    {
        $this->seed(ServiceTableSeeder::class);

        $this->expectException(ValidationException::class);
        Event::fake();

        $data = $this->data;
        $data['code'] = Service::first()->code;

        $job = new CreateNewService($data);
        $this->assertTrue($this->dispatch($job));

        Event::assertNotDispatched(NewServiceCreated::class);
    }
}
