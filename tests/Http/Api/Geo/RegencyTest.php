<?php

namespace Tests\Http\Api\Geo;

use Tests\TestCase;
use Database\Seeders\GeoTableSimpleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_on_simple_load()
    {
        $this->seed(GeoTableSimpleSeeder::class);
        $this->assertDatabaseCount('geo_regencies', 2);

        $response = $this->json('GET', route('api.geo'), [
            'type' => 'regency',
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
    }

    public function test_on_complex_query()
    {
        $this->seed(GeoTableSimpleSeeder::class);
        $this->assertDatabaseCount('geo_regencies', 2);

        // basic keyword search
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'regency',
            'q' => 'Lamongan',
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(0, $response->json('total'));

        // get regency by country
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'regency',
            'country_id' => 2,
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(0, $response->json('total'));

        // get regency by province
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'regency',
            'province_id' => 3,
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(0, $response->json('total'));

        // get province with existing keyword
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'regency',
            'q' => 'Surabaya',
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(1, $response->json('total'));
    }
}
