<?php

namespace Tests\Jobs\Handlings;

use App\Events\Handlings\HandlingModified;
use App\Jobs\Handlings\UpdateExistingHandling;
use App\Models\Handling;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HandlingUpdatingTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    protected array $updateData;

    public function setUp(): void
    {
        parent::setUp();

        $typeMapper = [Handling::TYPE_VOLUME,Handling::TYPE_WEIGHT];

        $this->updateData = [
            'name' => $this->faker->name,
            'price' => $this->faker->randomFloat(2,3000,10000),
            'type' => $typeMapper[$this->faker->randomKey([0,1])],
        ];

        Handling::factory(1)->create();
    }

    /**
     * Get Product Test Subject.
     *
     * @return \App\Models\Handling|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     */
    public function getTestSubject()
    {
        return Handling::query()->first();
    }

    public function test_on_valid_data()
    {
        $subject = $this->getTestSubject();
        Event::fake();

        $job = new UpdateExistingHandling($subject, $this->updateData);
        $this->assertTrue($this->dispatch($job));

        $this->assertDatabaseHas('handling', Arr::only($this->updateData, ['name']));
        Event::assertDispatched(HandlingModified::class);
    }

    public function test_on_invalid_data()
    {
        $subject = $this->getTestSubject();

        Event::fake();

        $this->expectException(ValidationException::class);
        $this->dispatch(new UpdateExistingHandling($subject, [
            'price' => $this->faker->name,
        ]));

        Event::assertNotDispatched(HandlingModified::class);
    }
}
