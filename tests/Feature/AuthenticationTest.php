<?php

namespace Theomessin\Tus\Tests\Core;

use Theomessin\Tus\Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /** @test */
    public function unauthenticated_users_receive_403_forbidden()
    {
        // Arrange: dispatch http requests as guest.
        $this->app['auth']->guard(null)->logout();

        // Act: dispatch a tus http request.
        $response = $this->post('/tus', [], []);

        // Assert: response is HTTP 403.
        $response->assertForbidden();
    }
}
