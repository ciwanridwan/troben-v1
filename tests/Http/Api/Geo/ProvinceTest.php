<?php

namespace Tests\Http\Api\Geo;

use Tests\TestCase;
use Database\Seeders\GeoTableSimpleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProvinceTest extends TestCase
{
    use RefreshDatabase;

    public function test_on_simple_load()
    {
        $this->seed(GeoTableSimpleSeeder::class);
        $this->assertDatabaseCount('geo_provinces', 2);

        $response = $this->json('GET', route('api.geo'), [
            'type' => 'province',
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
    }

    public function test_on_complex_query()
    {
        $this->seed(GeoTableSimpleSeeder::class);

        // basic keyword search
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'province',
            'q' => 'Papua',
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(0, $response->json('total'));

        // get province by country
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'province',
            'country_id' => 2,
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(0, $response->json('total'));

        // get province with existing keyword
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'province',
            'q' => 'Timur',
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(1, $response->json('total'));
    }
}
