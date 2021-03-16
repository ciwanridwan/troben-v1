<?php

namespace Tests\Jobs\Partners\Transporters;

use Tests\TestCase;
use App\Models\Partners\Partner;
use Illuminate\Support\Collection;
use App\Models\Partners\Transporter;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Validation\ValidationException;
use App\Jobs\Partners\Transporter\BulkTransporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Events\Partners\Transporter\TransporterBulked;

class TransporterBulkTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    private $data;
    private Partner $partner;
    private Collection $transporter;

    public function setUp(): void
    {
        parent::setUp();

        Partner::factory(1)->create([
            'type' => Partner::TYPE_BUSINESS,
        ]);

        $this->partner = Partner::first();

        $typeMapper = Transporter::getAvailableTypes();
        $this->data = [
            [
                'production_year' => $this->faker->year,
                'registration_name' => $this->faker->userName,
                'registration_year' => $this->faker->year,
                'registration_number' => $this->faker->lastName,
                'type' => $typeMapper[$this->faker->randomKey([0,1,2,3,4,5,6,7,8,9,10,11])],
            ],
            [
                'production_year' => $this->faker->year,
                'registration_name' => $this->faker->userName,
                'registration_year' => $this->faker->year,
                'registration_number' => $this->faker->lastName,
                'type' => $typeMapper[$this->faker->randomKey([0,1,2,3,4,5,6,7,8,9,10,11])],
            ],
            [
                'production_year' => $this->faker->year,
                'registration_name' => $this->faker->userName,
                'registration_year' => $this->faker->year,
                'registration_number' => $this->faker->lastName,
                'type' => $typeMapper[$this->faker->randomKey([0,1,2,3,4,5,6,7,8,9,10,11])],
            ],
        ];
    }

    public function test_on_valid_data()
    {
        Event::fake();

        $job = new BulkTransporter($this->partner, $this->data);

        $this->assertTrue($this->dispatch($job));

        $this->assertInstanceOf(Collection::class, $job->transporters);

        $this->assertTrue($job->finish);

        $this->assertDatabaseCount('transporters', $job->transporters->count());

        Event::assertDispatched(TransporterBulked::class);
    }

    public function test_on_missing_data()
    {
        $this->expectException(ValidationException::class);
        Event::fake();

        $data = collect($this->data)->map(fn ($item) => ['registration_name' => $item['registration_name'], 'registration_number' => $item['registration_number']])->toArray();

        $job = new BulkTransporter($this->partner, $data);
        $this->assertTrue($this->dispatch($job));

        Event::assertNotDispatched(TransporterBulked::class);
    }

    public function test_on_invalid_data()
    {
        $this->expectException(ValidationException::class);
        Event::fake();

        $data = collect($this->data)->map(fn ($item) => ['registration_name' => $item['registration_name'], 'registration_number' => $item['registration_number'], 'type' => 'aaddcc'])->toArray();

        $job = new BulkTransporter($this->partner, $data);
        $this->assertTrue($this->dispatch($job));

        Event::assertNotDispatched(TransporterBulked::class);
    }
}
