<?php

namespace Tests\Http\Api\Geo;

use Tests\TestCase;
use App\Http\Response;
use Database\Seeders\GeoTableSimpleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CountryTest extends TestCase
{
    use RefreshDatabase;

    public function test_on_missing_parameters()
    {
        $this->assertDatabaseCount('geo_countries', 0);

        $response = $this->json('GET', route('api.geo'), [], $this->getCustomersHeader());
        $expected = new Response(Response::RC_INVALID_DATA);
        $response->assertStatus($expected->resolveHttpCode());
        $response->assertJsonStructure(array_keys($expected->getResponseData(request())));
    }

    public function test_on_simple_load()
    {
        $this->seed(GeoTableSimpleSeeder::class);
        $this->assertDatabaseCount('geo_countries', 1);

        $response = $this->json('GET', route('api.geo'), [
            'type' => 'country',
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
    }

    public function test_on_complex_query()
    {
        $this->seed(GeoTableSimpleSeeder::class);
        $this->assertDatabaseCount('geo_countries', 1);

        $response = $this->json('GET', route('api.geo'), [
            'type' => 'country',
            'q' => 'Zimbabwe',
        ], $this->getCustomersHeader());

        $this->assertSuccessResponse($response);
        $this->assertEquals(0, $response->json('total'));
    }
}
