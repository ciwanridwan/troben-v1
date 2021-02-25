<?php

namespace Tests\Jobs\Handlings;

use Tests\TestCase;
use App\Models\Handling;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use App\Jobs\Handlings\CreateNewHandling;
use App\Events\Handlings\NewHandlingCreated;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HandlingCreationTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    private $data;
    private Handling $handling;

    public function setUp(): void
    {
        parent::setUp();

        $typeMapper = [Handling::TYPE_VOLUME,Handling::TYPE_WEIGHT];

        $this->data = [
            'name' => $this->faker->name,
            'price' => $this->faker->randomFloat(2, 3000, 10000),
            'type' => $typeMapper[$this->faker->randomKey([0,1])],
        ];
    }

    public function test_on_valid_data()
    {
        Event::fake();

        $job = new CreateNewHandling($this->data);

        $this->assertTrue($this->dispatch($job));

        $this->assertInstanceOf(Handling::class, $job->handling);

        $this->assertTrue($job->handling->exists);

        $this->assertDatabaseHas('handling', Arr::only($this->data, ['name']));

        Event::assertDispatched(NewHandlingCreated::class);
    }

    public function test_on_missing_data()
    {
        $this->expectException(ValidationException::class);
        Event::fake();

        $data = Arr::only($this->data, ['price','type']);

        $job = new CreateNewHandling($data);
        $this->assertTrue($this->dispatch($job));

        Event::assertNotDispatched(NewHandlingCreated::class);
    }
}
