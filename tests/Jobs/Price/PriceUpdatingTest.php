<?php

namespace Tests\Jobs\Price;

use Tests\TestCase;
use App\Models\Price;
use App\Models\Service;
use Illuminate\Support\Arr;
use App\Models\Geo\SubDistrict;
use App\Events\Price\PriceModified;
use Illuminate\Support\Facades\Event;
use App\Jobs\Price\UpdateExistingPrice;
use Database\Seeders\ServiceTableSeeder;
use Database\Seeders\GeoTableSimpleSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PriceUpdatingTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    protected array $updateData;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed([GeoTableSimpleSeeder::class,ServiceTableSeeder::class]);

        $origin = SubDistrict::first();
        $destination = SubDistrict::latest('id')->first();
        $this->updateData = [
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
        Price::factory(1)->create([
            'origin_province_id' => $origin->province_id,
            'origin_regency_id' => $origin->regency_id,
            'origin_district_id' => $origin->district_id,
            'origin_sub_district_id' => $origin->id,
            'destination_id' => $destination->id,
            'zip_code' => $destination->zip_code,
            'service_code' => Service::latest('id')->first()->code,
        ]);
    }

    /**
     * Get Price Test Subject.
     *
     * @return \App\Models\Price|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     */
    public function getTestSubject()
    {
        return Price::query()->first();
    }

    public function test_on_valid_data()
    {
        $subject = $this->getTestSubject();
        Event::fake();

        $job = new UpdateExistingPrice($subject, $this->updateData);
        $this->assertTrue($this->dispatch($job));

        $this->assertDatabaseHas('prices', Arr::only($this->updateData, ['zip_code']));
        Event::assertDispatched(PriceModified::class);
    }

    public function test_on_invalid_data()
    {
        $subject = $this->getTestSubject();

        Event::fake();

        $this->expectException(ValidationException::class);
        $this->dispatch(new UpdateExistingPrice($subject, [
            'service_code' => 'aaaaa',
        ]));

        Event::assertNotDispatched(PriceModified::class);
    }
}
