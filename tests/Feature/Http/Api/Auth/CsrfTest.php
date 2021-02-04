<?php

namespace Tests\Feature\Http\Api\Auth;

use Tests\TestCase;

class CsrfTest extends TestCase
{
    public function test_csrf_token()
    {
        $response = $this->get('/sanctum/csrf-cookie');

        // assert status to no content
        $response->assertNoContent();

        // assert cookies is correctly assigned.
        $response->assertCookieNotExpired('XSRF-TOKEN');
        $response->assertCookieNotExpired(config('session.cookie'));
    }
}
