<?php

namespace Tests\Jobs\Price;

use Tests\TestCase;
use App\Models\Price;
use App\Models\Service;
use Illuminate\Support\Arr;
use App\Models\Geo\SubDistrict;
use App\Jobs\Price\CreateNewPrice;
use App\Events\Price\NewPriceCreated;
use Illuminate\Support\Facades\Event;
use Database\Seeders\ServiceTableSeeder;
use Database\Seeders\GeoTableSimpleSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PriceCreationTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    private $data;
    private Price $price;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed([GeoTableSimpleSeeder::class,ServiceTableSeeder::class]);

        $origin = SubDistrict::first();
        $destination = SubDistrict::latest('id')->first();
        $this->data = [
            'origin_province_id' => $origin->province_id,
            'origin_regency_id' => $origin->regency_id,
            'origin_district_id' => $origin->district_id,
            'origin_sub_district_id' => $origin->id,
            'destination_id' => $destination->id,
            'zip_code' => $destination->zip_code,
            'service_code' => Service::first()->code,
            'tier_1' => 15000,
            'tier_2' => 12000,
            'tier_3' => 10000,
            'tier_4' => 8000,
            'tier_5' => 4000,
            'tier_6' => 2500,
            'tier_7' => 0,
            'tier_8' => 0,
            'tier_9' => 0,
            'tier_10' => 0,
        ];
    }

    public function test_on_valid_data()
    {
        Event::fake();

        $job = new CreateNewPrice($this->data);

        $this->assertTrue($this->dispatch($job));

        $this->assertInstanceOf(Price::class, $job->price);

        $this->assertTrue($job->price->exists);

        $this->assertDatabaseHas('prices', Arr::only($this->data, ['zip_code']));

        Event::assertDispatched(NewPriceCreated::class);
    }

    public function test_on_missing_data()
    {
        $this->expectException(ValidationException::class);
        Event::fake();

        $data = Arr::only($this->data, ['zip_code','service_code']);

        $job = new CreateNewPrice($data);
        $this->assertTrue($this->dispatch($job));

        Event::assertNotDispatched(NewPriceCreated::class);
    }

    public function test_on_invalid_data()
    {
        $this->expectException(ValidationException::class);
        Event::fake();

        $data = $this->data;
        $data['origin_province_id'] = 'abcd';

        $job = new CreateNewPrice($data);
        $this->assertTrue($this->dispatch($job));

        Event::assertNotDispatched(NewPriceCreated::class);
    }
}
