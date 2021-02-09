<?php

namespace Tests\Http\Api\Geo;

use Tests\TestCase;
use Database\Seeders\GeoTableSimpleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubDistrictTest extends TestCase
{
    use RefreshDatabase;

    public function test_on_simple_load()
    {
        $this->seed(GeoTableSimpleSeeder::class);

        $response = $this->json('GET', route('api.geo'), [
            'type' => 'sub_district',
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
    }

    public function test_on_complex_query()
    {
        $this->seed(GeoTableSimpleSeeder::class);

        // basic keyword search
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'sub_district',
            'q' => 'Paciran',
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(0, $response->json('total'));

        // get sub district by country
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'sub_district',
            'country_id' => 2,
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(0, $response->json('total'));

        // get sub district by province
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'sub_district',
            'country_id' => 1,
            'province_id' => 3,
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(0, $response->json('total'));

        // get sub district by regency
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'sub_district',
            'country_id' => 1,
            'province_id' => 2,
            'regency_id' => 3,
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(0, $response->json('total'));

        // get sub district by district
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'sub_district',
            'country_id' => 1,
            'province_id' => 2,
            'regency_id' => 1,
            'district_id' => 100,
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(0, $response->json('total'));

        // get sub district with existing keyword
        $response = $this->json('GET', route('api.geo'), [
            'type' => 'sub_district',
            'q' => 'Wonorejo',
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(2, $response->json('total'));

        $response = $this->json('GET', route('api.geo'), [
            'type' => 'sub_district',
            'zip_code' => '60213',
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(2, $response->json('total'));
    }
}
