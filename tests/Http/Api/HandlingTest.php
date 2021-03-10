<?php

namespace Tests\Http\Api;

use Tests\TestCase;
use Database\Seeders\HandlingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HandlingTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public function test_can_get_handling()
    {
        $this->seed(HandlingSeeder::class);

        $response = $this->getJson(route('api.handling'), $this->getCustomersHeader());

        $response->assertOk();
    }
}
