<?php

namespace Tests\Jobs\Price;

use App\Events\Price\PriceDeleted;
use App\Jobs\Price\DeleteExistingPrice;
use App\Models\Geo\SubDistrict;
use App\Models\Price;
use App\Models\Service;
use Database\Seeders\GeoTableSimpleSeeder;
use Database\Seeders\ServiceTableSeeder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PriceDeletionTest extends TestCase
{
    use RefreshDatabase,DispatchesJobs;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed([GeoTableSimpleSeeder::class,ServiceTableSeeder::class]);

        $origin = SubDistrict::first();
        $destination = SubDistrict::latest('id')->first();
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
    public function test_on_force_delete()
    {
        Event::fake();

        /** @var \App\Models\Price $subject */
        $subject = $this->getTestSubject();
        $response = $this->dispatch(new DeleteExistingPrice($subject, true));
        $this->assertTrue($response);
        $this->assertDatabaseMissing('services', Arr::only($subject->toArray(), 'zip_code'));

        Event::assertDispatched(PriceDeleted::class);
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
}
