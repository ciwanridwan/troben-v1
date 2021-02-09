<?php

namespace Tests\Http\Api\Geo;

use Tests\TestCase;
use Database\Seeders\GeoTableSimpleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DistrictTest extends TestCase
{
    use RefreshDatabase;

    public function test_on_simple_load()
    {
        $this->seed(GeoTableSimpleSeeder::class);

        $response = $this->json('GET', route('api.geo'), [
            'type' => 'district',
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
    }

    public function test_on_complex_query()
    {
        $this->seed(GeoTableSimpleSeeder::class);

        // basic keyword search
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'district',
            'q' => 'Paciran',
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(0, $response->json('total'));

        // get district by country
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'district',
            'country_id' => 2,
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(0, $response->json('total'));

        // get district by province
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'district',
            'country_id' => 1,
            'province_id' => 3,
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(0, $response->json('total'));

        // get district by regency
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'district',
            'country_id' => 1,
            'province_id' => 2,
            'regency_id' => 100,
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(0, $response->json('total'));

        // get district with existing keyword
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'district',
            'q' => 'Gubeng',
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(1, $response->json('total'));
    }
}
