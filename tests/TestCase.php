<?php

namespace Tests;

use Illuminate\Testing\TestResponse;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Assert successful response.
     *
     * @param \Illuminate\Testing\TestResponse $response
     */
    public function assertSuccessResponse(TestResponse $response)
    {
        $response->assertOk();

        $response->assertJsonStructure([
            'code',
            'error',
            'message',
            'data',
        ]);

        $this->assertEquals(0, $response->json('code'));
        $this->assertNull($response->json('error'));
    }
}
